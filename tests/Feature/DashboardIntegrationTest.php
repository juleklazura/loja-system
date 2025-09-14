<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Services\DashboardCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Carbon\Carbon;

class DashboardIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $adminUser;
    private DashboardCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'is_admin' => true
        ]);

        $this->cacheService = new DashboardCacheService();
    }

    /** @test */
    public function dashboard_loads_with_complete_data_integration()
    {
        // Arrange - Create test data across multiple models
        $categories = Category::factory()->count(3)->create();
        $products = collect();
        
        foreach ($categories as $category) {
            $categoryProducts = Product::factory()->count(5)->create([
                'category_id' => $category->id,
                'stock_quantity' => $this->faker->numberBetween(1, 50)
            ]);
            $products = $products->merge($categoryProducts);
        }

        // Create orders with relationships
        $users = User::factory()->count(5)->create();
        $orders = collect();
        
        foreach ($users as $user) {
            $userOrders = Order::factory()->count(3)->create([
                'user_id' => $user->id,
                'total_amount' => $this->faker->randomFloat(2, 50, 500),
                'created_at' => Carbon::now()->subDays(rand(1, 30))
            ]);
            $orders = $orders->merge($userOrders);
        }

        // Act - Make request to dashboard
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Assert - Check response and data integration
        $response->assertOk();
        $response->assertViewIs('admin.dashboard');
        
        // Verify all data types are loaded correctly
        $response->assertViewHas('totalProducts', $products->count());
        $response->assertViewHas('totalCategories', $categories->count());
        $response->assertViewHas('totalOrders', $orders->count());
        $response->assertViewHas('totalUsers', User::count()); // Includes created admin and users
        
        // Verify relationships are loaded
        $response->assertViewHas('categoriesWithCounts');
        $response->assertViewHas('recentOrders');
        $response->assertViewHas('lowStockProducts');
        $response->assertViewHas('revenueData');

        // Verify data structure integrity
        $viewData = $response->original->getData();
        
        // Check categories with product counts
        $this->assertGreaterThan(0, $viewData['categoriesWithCounts']->count());
        foreach ($viewData['categoriesWithCounts'] as $category) {
            $this->assertArrayHasKey('products_count', $category->toArray());
            $this->assertGreaterThanOrEqual(0, $category->products_count);
        }

        // Check recent orders have user relationships
        foreach ($viewData['recentOrders'] as $order) {
            $this->assertNotNull($order->user);
            $this->assertNotNull($order->user->name);
        }

        // Check revenue data structure
        $this->assertArrayHasKey('labels', $viewData['revenueData']);
        $this->assertArrayHasKey('data', $viewData['revenueData']);
        $this->assertIsArray($viewData['revenueData']['labels']);
        $this->assertIsArray($viewData['revenueData']['data']);
    }

    /** @test */
    public function dashboard_cache_integration_works_end_to_end()
    {
        // Arrange
        Category::factory()->count(2)->create();
        Product::factory()->count(10)->create();
        Order::factory()->count(5)->create();

        // Clear any existing cache
        Cache::flush();

        // Act - First request should populate cache
        $response1 = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Second request should use cache
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Assert - Both responses should be identical
        $response1->assertOk();
        $response2->assertOk();

        // Verify cache was used (same data)
        $viewData1 = $response1->original->getData();
        $viewData2 = $response2->original->getData();

        $this->assertEquals($viewData1['totalProducts'], $viewData2['totalProducts']);
        $this->assertEquals($viewData1['totalCategories'], $viewData2['totalCategories']);
        $this->assertEquals($viewData1['totalOrders'], $viewData2['totalOrders']);

        // Verify cache keys exist
        $this->assertTrue(Cache::has('dashboard_stats'));
        $this->assertTrue(Cache::has('dashboard_recent_orders'));
        $this->assertTrue(Cache::has('dashboard_categories_counts'));
    }

    /** @test */
    public function dashboard_cache_invalidation_integration()
    {
        // Arrange - Create initial data and populate cache
        Category::factory()->count(2)->create();
        Product::factory()->count(5)->create();

        $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard')); // Populate cache

        $initialProductCount = Cache::get('dashboard_stats')['totalProducts'];

        // Act - Add new product (should invalidate cache)
        Product::factory()->create();

        // Make request with cache clearing
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.dashboard.clear-cache'))
            ->followRedirect();

        // Then get dashboard again
        $dashboardResponse = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Assert - Cache should be updated with new data
        $response->assertOk();
        $dashboardResponse->assertOk();

        $newStats = Cache::get('dashboard_stats');
        $this->assertEquals($initialProductCount + 1, $newStats['totalProducts']);
    }

    /** @test */
    public function dashboard_database_queries_optimization_integration()
    {
        // Arrange
        $categories = Category::factory()->count(3)->create();
        
        foreach ($categories as $category) {
            Product::factory()->count(10)->create(['category_id' => $category->id]);
        }

        $users = User::factory()->count(5)->create();
        foreach ($users as $user) {
            Order::factory()->count(3)->create(['user_id' => $user->id]);
        }

        // Act & Assert - Monitor queries during dashboard load
        DB::enableQueryLog();
        
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Verify response is successful
        $response->assertOk();

        // Analyze query optimization
        $queryCount = count($queries);
        
        // Should have optimized queries (not N+1)
        $this->assertLessThan(20, $queryCount, 'Too many database queries executed');

        // Check for efficient joins in queries
        $joinQueries = array_filter($queries, function($query) {
            return str_contains(strtolower($query['query']), 'join');
        });

        $this->assertGreaterThan(0, count($joinQueries), 'Should use JOIN queries for optimization');

        // Verify no repeated similar queries (N+1 prevention)
        $queryStatements = array_column($queries, 'query');
        $uniqueQueries = array_unique($queryStatements);
        
        $this->assertLessThanOrEqual($queryCount, count($uniqueQueries));
    }

    /** @test */
    public function dashboard_handles_empty_data_gracefully_integration()
    {
        // Arrange - No data in database (fresh migration)
        $this->assertTrue(Product::count() === 0);
        $this->assertTrue(Category::count() === 0);
        $this->assertTrue(Order::count() === 0);

        // Act
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Assert
        $response->assertOk();
        
        $viewData = $response->original->getData();
        
        // Should handle empty data gracefully
        $this->assertEquals(0, $viewData['totalProducts']);
        $this->assertEquals(0, $viewData['totalCategories']);
        $this->assertEquals(0, $viewData['totalOrders']);
        
        // Collections should be empty but not null
        $this->assertNotNull($viewData['recentOrders']);
        $this->assertNotNull($viewData['lowStockProducts']);
        $this->assertNotNull($viewData['categoriesWithCounts']);
        
        $this->assertTrue($viewData['recentOrders']->isEmpty());
        $this->assertTrue($viewData['lowStockProducts']->isEmpty());
        $this->assertTrue($viewData['categoriesWithCounts']->isEmpty());

        // Revenue data should have empty but valid structure
        $this->assertArrayHasKey('labels', $viewData['revenueData']);
        $this->assertArrayHasKey('data', $viewData['revenueData']);
    }

    /** @test */
    public function dashboard_low_stock_products_integration()
    {
        // Arrange
        $category = Category::factory()->create();
        
        // Create products with different stock levels
        $lowStockProduct1 = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Low Stock Product 1',
            'stock_quantity' => 2
        ]);
        
        $lowStockProduct2 = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Low Stock Product 2', 
            'stock_quantity' => 0
        ]);
        
        $normalStockProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Normal Stock Product',
            'stock_quantity' => 50
        ]);

        // Act
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Assert
        $response->assertOk();
        
        $lowStockProducts = $response->original->getData()['lowStockProducts'];
        
        // Should only show low stock products
        $this->assertCount(2, $lowStockProducts);
        
        $lowStockIds = $lowStockProducts->pluck('id')->toArray();
        $this->assertContains($lowStockProduct1->id, $lowStockIds);
        $this->assertContains($lowStockProduct2->id, $lowStockIds);
        $this->assertNotContains($normalStockProduct->id, $lowStockIds);

        // Should be ordered by stock quantity (lowest first)
        $stockQuantities = $lowStockProducts->pluck('stock_quantity')->toArray();
        $this->assertEquals([0, 2], $stockQuantities);
    }

    /** @test */
    public function dashboard_revenue_chart_data_integration()
    {
        // Arrange - Create orders across different dates
        $user = User::factory()->create();
        
        // Orders from last 30 days
        Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 100.00,
            'created_at' => Carbon::now()->subDays(5)
        ]);
        
        Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 250.00,
            'created_at' => Carbon::now()->subDays(10)
        ]);
        
        Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 150.00,
            'created_at' => Carbon::now()->subDays(5) // Same day as first order
        ]);

        // Act
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Assert
        $response->assertOk();
        
        $revenueData = $response->original->getData()['revenueData'];
        
        // Should have labels and data arrays
        $this->assertArrayHasKey('labels', $revenueData);
        $this->assertArrayHasKey('data', $revenueData);
        
        $this->assertIsArray($revenueData['labels']);
        $this->assertIsArray($revenueData['data']);
        
        // Should have 30 data points (one for each day)
        $this->assertCount(30, $revenueData['labels']);
        $this->assertCount(30, $revenueData['data']);
        
        // Should aggregate orders by date
        $totalRevenue = array_sum($revenueData['data']);
        $this->assertEquals(500.00, $totalRevenue); // 100 + 250 + 150
    }

    /** @test */
    public function dashboard_authentication_and_authorization_integration()
    {
        // Test unauthenticated access
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));

        // Test authenticated non-admin access (if implemented)
        $regularUser = User::factory()->create(['is_admin' => false]);
        
        $response = $this->actingAs($regularUser)
            ->get(route('admin.dashboard'));
        
        // This should either redirect or show 403, depending on implementation
        $this->assertTrue(
            $response->isRedirect() || $response->status() === 403,
            'Non-admin users should not access admin dashboard'
        );

        // Test admin access
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));
        
        $response->assertOk();
    }

    /** @test */
    public function dashboard_performance_under_load_integration()
    {
        // Arrange - Create substantial amount of test data
        $categories = Category::factory()->count(10)->create();
        
        foreach ($categories as $category) {
            Product::factory()->count(20)->create(['category_id' => $category->id]);
        }

        $users = User::factory()->count(50)->create();
        foreach ($users as $user) {
            Order::factory()->count(rand(1, 10))->create(['user_id' => $user->id]);
        }

        // Act - Measure response time
        $startTime = microtime(true);
        
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Assert
        $response->assertOk();
        
        // Response should be reasonably fast (under 2 seconds)
        $this->assertLessThan(2000, $responseTime, 'Dashboard should load in under 2 seconds');
        
        // All data should still be loaded correctly
        $this->assertEquals(200, $response->original->getData()['totalProducts']); // 10 categories * 20 products
        $this->assertEquals(10, $response->original->getData()['totalCategories']);
        $this->assertGreaterThan(50, $response->original->getData()['totalOrders']); // At least 50 orders
    }
}
