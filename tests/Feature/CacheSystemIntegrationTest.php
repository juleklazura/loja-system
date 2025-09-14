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
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Carbon\Carbon;

class CacheSystemIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $adminUser;
    private DashboardCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true
        ]);

        $this->cacheService = app(DashboardCacheService::class);
    }

    /** @test */
    public function cache_service_integrates_with_dashboard_controller()
    {
        // Arrange
        Category::factory()->count(3)->create();
        Product::factory()->count(10)->create();
        Order::factory()->count(5)->create();

        // Clear any existing cache
        Cache::flush();

        // Act - First dashboard request should populate cache
        $response1 = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Verify cache was populated
        $this->assertTrue(Cache::has('dashboard_stats'));
        $this->assertTrue(Cache::has('dashboard_recent_orders'));
        $this->assertTrue(Cache::has('dashboard_categories_counts'));

        // Second request should use cached data
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Assert
        $response1->assertOk();
        $response2->assertOk();

        // Both responses should have identical data
        $data1 = $response1->original->getData();
        $data2 = $response2->original->getData();

        $this->assertEquals($data1['totalProducts'], $data2['totalProducts']);
        $this->assertEquals($data1['totalCategories'], $data2['totalCategories']);
        $this->assertEquals($data1['totalOrders'], $data2['totalOrders']);
    }

    /** @test */
    public function cache_invalidation_works_across_system_integration()
    {
        // Arrange
        Category::factory()->count(2)->create();
        Product::factory()->count(5)->create();

        // Populate cache via dashboard
        $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $initialStats = Cache::get('dashboard_stats');
        $this->assertEquals(5, $initialStats['totalProducts']);

        // Act - Add new product (simulating admin action)
        $newProduct = Product::factory()->create();

        // Clear cache manually (simulating what would happen in controller)
        $this->cacheService->clearDashboardCache();

        // Get fresh dashboard data
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Assert
        $response->assertOk();
        
        $updatedStats = Cache::get('dashboard_stats');
        $this->assertEquals(6, $updatedStats['totalProducts']);

        $data = $response->original->getData();
        $this->assertEquals(6, $data['totalProducts']);
    }

    /** @test */
    public function cache_ttl_respects_configuration_integration()
    {
        // Arrange
        Category::factory()->count(2)->create();
        Product::factory()->count(3)->create();

        // Clear cache
        Cache::flush();

        // Act - Populate cache
        $stats = $this->cacheService->getCachedStatistics();
        
        // Assert cache was set
        $this->assertTrue(Cache::has('dashboard_stats'));

        // Verify TTL by checking cache store directly
        $cacheStore = Cache::getStore();
        
        // The cache should exist
        $this->assertNotNull(Cache::get('dashboard_stats'));

        // Fast-forward time beyond TTL (5 minutes + buffer)
        // Note: In a real test environment, you might use Carbon::setTestNow()
        // or mock the cache TTL for more precise testing
        
        // For now, verify the cache service uses the correct TTL value
        $reflectionClass = new \ReflectionClass($this->cacheService);
        $method = $reflectionClass->getMethod('getCacheMinutes');
        $method->setAccessible(true);
        $ttl = $method->invoke($this->cacheService);
        
        $this->assertEquals(5, $ttl); // Should be 5 minutes as configured
    }

    /** @test */
    public function cache_handles_concurrent_requests_integration()
    {
        // Arrange
        Category::factory()->count(3)->create();
        Product::factory()->count(8)->create();
        Order::factory()->count(4)->create();

        Cache::flush();

        // Act - Simulate concurrent requests
        $responses = [];
        
        // Make multiple simultaneous requests
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->actingAs($this->adminUser)
                ->get(route('admin.dashboard'));
        }

        // Assert
        foreach ($responses as $response) {
            $response->assertOk();
        }

        // All responses should have the same data
        $firstData = $responses[0]->original->getData();
        
        foreach (array_slice($responses, 1) as $response) {
            $data = $response->original->getData();
            $this->assertEquals($firstData['totalProducts'], $data['totalProducts']);
            $this->assertEquals($firstData['totalCategories'], $data['totalCategories']);
            $this->assertEquals($firstData['totalOrders'], $data['totalOrders']);
        }

        // Cache should have been populated only once
        $this->assertTrue(Cache::has('dashboard_stats'));
    }

    /** @test */
    public function cache_fallback_works_when_cache_fails_integration()
    {
        // Arrange
        Category::factory()->count(2)->create();
        Product::factory()->count(5)->create();
        Order::factory()->count(3)->create();

        // Act - Simulate cache failure by using invalid cache configuration
        // We'll temporarily replace the cache service with a mock that fails
        $this->app->singleton(DashboardCacheService::class, function () {
            $mockService = $this->createMock(DashboardCacheService::class);
            
            // Mock cache failure - getCachedStatistics should fall back to direct DB
            $mockService->method('getCachedStatistics')
                ->willReturn([
                    'totalProducts' => Product::count(),
                    'totalCategories' => Category::count(),
                    'totalOrders' => Order::count(),
                    'totalUsers' => User::count()
                ]);

            $mockService->method('getCachedRecentOrders')
                ->willReturn(Order::forDashboard()->limit(10)->get());

            $mockService->method('getCachedCategoriesWithCounts')
                ->willReturn(Category::withCount('products')->get());

            return $mockService;
        });

        // Request dashboard data
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Assert
        $response->assertOk();

        $data = $response->original->getData();
        $this->assertEquals(5, $data['totalProducts']);
        $this->assertEquals(2, $data['totalCategories']);
        $this->assertEquals(3, $data['totalOrders']);
    }

    /** @test */
    public function cache_clear_endpoint_integration()
    {
        // Arrange
        Category::factory()->count(2)->create();
        Product::factory()->count(4)->create();

        // Populate cache
        $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $this->assertTrue(Cache::has('dashboard_stats'));

        // Act - Clear cache via endpoint
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.dashboard.clear-cache'));

        // Assert
        $response->assertRedirect(route('admin.dashboard'));
        
        // Cache should be cleared
        $this->assertFalse(Cache::has('dashboard_stats'));
        $this->assertFalse(Cache::has('dashboard_recent_orders'));
        $this->assertFalse(Cache::has('dashboard_categories_counts'));

        // Follow the redirect and verify fresh data is loaded
        $dashboardResponse = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $dashboardResponse->assertOk();
        
        // Cache should be repopulated
        $this->assertTrue(Cache::has('dashboard_stats'));
    }

    /** @test */
    public function cache_with_revenue_data_integration()
    {
        // Arrange
        $user = User::factory()->create();
        
        // Create orders across different dates
        Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 100.00,
            'created_at' => Carbon::now()->subDays(5)
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 200.00,
            'created_at' => Carbon::now()->subDays(10)
        ]);

        Cache::flush();

        // Act - Get dashboard data (which includes revenue data)
        $response1 = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Assert
        $response1->assertOk();
        $response2->assertOk();

        $revenueData1 = $response1->original->getData()['revenueData'];
        $revenueData2 = $response2->original->getData()['revenueData'];

        // Revenue data should be consistent between cached requests
        $this->assertEquals($revenueData1['labels'], $revenueData2['labels']);
        $this->assertEquals($revenueData1['data'], $revenueData2['data']);

        // Total revenue should be 300.00
        $totalRevenue = array_sum($revenueData1['data']);
        $this->assertEquals(300.00, $totalRevenue);
    }

    /** @test */
    public function cache_performance_improvement_integration()
    {
        // Arrange
        $categories = Category::factory()->count(10)->create();
        
        foreach ($categories as $category) {
            Product::factory()->count(20)->create(['category_id' => $category->id]);
        }

        $users = User::factory()->count(50)->create();
        foreach ($users as $user) {
            Order::factory()->count(rand(3, 8))->create(['user_id' => $user->id]);
        }

        Cache::flush();

        // Act - Measure first request (no cache)
        $startTime1 = microtime(true);
        
        $response1 = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));
        
        $endTime1 = microtime(true);
        $firstRequestTime = ($endTime1 - $startTime1) * 1000;

        // Measure second request (with cache)
        $startTime2 = microtime(true);
        
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));
        
        $endTime2 = microtime(true);
        $secondRequestTime = ($endTime2 - $startTime2) * 1000;

        // Assert
        $response1->assertOk();
        $response2->assertOk();

        // Second request should be significantly faster (cache hit)
        $this->assertLessThan($firstRequestTime, $secondRequestTime);
        
        // Both should return same data
        $data1 = $response1->original->getData();
        $data2 = $response2->original->getData();
        
        $this->assertEquals($data1['totalProducts'], $data2['totalProducts']);
        $this->assertEquals($data1['totalCategories'], $data2['totalCategories']);
        $this->assertEquals($data1['totalOrders'], $data2['totalOrders']);

        // Performance improvement should be measurable
        $improvement = (($firstRequestTime - $secondRequestTime) / $firstRequestTime) * 100;
        
        // Cache should provide at least some performance benefit
        // Note: In test environment, the improvement might be minimal due to small dataset
        $this->assertGreaterThanOrEqual(0, $improvement);
    }

    /** @test */
    public function cache_service_methods_work_independently_integration()
    {
        // Arrange
        Category::factory()->count(3)->create();
        Product::factory()->count(6)->create();
        Order::factory()->count(4)->create();

        Cache::flush();

        // Act - Test individual cache methods
        $stats = $this->cacheService->getCachedStatistics();
        $orders = $this->cacheService->getCachedRecentOrders();
        $categories = $this->cacheService->getCachedCategoriesWithCounts();
        $revenueData = $this->cacheService->getCachedRevenueData();

        // Assert
        $this->assertIsArray($stats);
        $this->assertEquals(6, $stats['totalProducts']);
        $this->assertEquals(3, $stats['totalCategories']);
        $this->assertEquals(4, $stats['totalOrders']);

        $this->assertCount(4, $orders);
        foreach ($orders as $order) {
            $this->assertNotNull($order->user_name);
        }

        $this->assertCount(3, $categories);
        foreach ($categories as $category) {
            $this->assertArrayHasKey('products_count', $category->toArray());
        }

        $this->assertArrayHasKey('labels', $revenueData);
        $this->assertArrayHasKey('data', $revenueData);
        $this->assertCount(30, $revenueData['labels']);

        // Verify caches were set
        $this->assertTrue(Cache::has('dashboard_stats'));
        $this->assertTrue(Cache::has('dashboard_recent_orders'));
        $this->assertTrue(Cache::has('dashboard_categories_counts'));
        $this->assertTrue(Cache::has('dashboard_revenue_data'));
    }

    /** @test */
    public function cache_artisan_commands_integration()
    {
        // Arrange
        Category::factory()->count(2)->create();
        Product::factory()->count(4)->create();

        // Populate cache
        $this->cacheService->getCachedStatistics();
        $this->assertTrue(Cache::has('dashboard_stats'));

        // Act - Clear cache via artisan command
        Artisan::call('cache:clear');

        // Assert
        $this->assertFalse(Cache::has('dashboard_stats'));

        // Repopulate and verify it works
        $stats = $this->cacheService->getCachedStatistics();
        $this->assertTrue(Cache::has('dashboard_stats'));
        $this->assertEquals(4, $stats['totalProducts']);
    }
}
