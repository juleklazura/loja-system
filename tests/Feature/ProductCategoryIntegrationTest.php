<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductCategoryIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true
        ]);
    }

    /** @test */
    public function product_creation_with_category_relationship_integration()
    {
        // Arrange
        $category = Category::factory()->create([
            'name' => 'Electronics',
            'active' => true
        ]);

        // Act - Create product via controller
        $productData = [
            'name' => 'Test Smartphone',
            'description' => 'A test smartphone product',
            'price' => 699.99,
            'stock_quantity' => 50,
            'sku' => 'PHONE-001',
            'category_id' => $category->id,
            'active' => true
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.products.store'), $productData);

        // Assert
        $response->assertRedirect();
        
        // Verify product was created in database
        $this->assertDatabaseHas('products', [
            'name' => 'Test Smartphone',
            'category_id' => $category->id,
            'sku' => 'PHONE-001'
        ]);

        // Verify relationship works
        $product = Product::where('sku', 'PHONE-001')->first();
        $this->assertNotNull($product);
        $this->assertEquals($category->id, $product->category_id);
        $this->assertEquals('Electronics', $product->category->name);

        // Verify reverse relationship
        $this->assertTrue($category->products->contains($product));
    }

    /** @test */
    public function category_deletion_with_products_cascade_integration()
    {
        // Arrange
        $category = Category::factory()->create();
        $products = Product::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertCount(3, $category->products);

        // Act - Delete category
        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.categories.destroy', $category));

        // Assert
        $response->assertRedirect();
        
        // Verify category is deleted
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        
        // Verify products are also deleted (cascade)
        foreach ($products as $product) {
            $this->assertDatabaseMissing('products', ['id' => $product->id]);
        }
    }

    /** @test */
    public function product_search_with_category_filter_integration()
    {
        // Arrange
        $electronicsCategory = Category::factory()->create(['name' => 'Electronics']);
        $booksCategory = Category::factory()->create(['name' => 'Books']);

        $smartphone = Product::factory()->create([
            'name' => 'iPhone 15',
            'category_id' => $electronicsCategory->id
        ]);

        $laptop = Product::factory()->create([
            'name' => 'MacBook Pro',
            'category_id' => $electronicsCategory->id
        ]);

        $book = Product::factory()->create([
            'name' => 'Laravel Guide',
            'category_id' => $booksCategory->id
        ]);

        // Act - Search products with category filter
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.products.index', [
                'category_id' => $electronicsCategory->id
            ]));

        // Assert
        $response->assertOk();
        $products = $response->original->getData()['products'] ?? collect();

        // Should only show electronics products
        $this->assertCount(2, $products);
        
        $productIds = $products->pluck('id')->toArray();
        $this->assertContains($smartphone->id, $productIds);
        $this->assertContains($laptop->id, $productIds);
        $this->assertNotContains($book->id, $productIds);
    }

    /** @test */
    public function product_stock_management_integration()
    {
        // Arrange
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10,
            'min_stock' => 5
        ]);

        $user = User::factory()->create();

        // Act - Create order that reduces stock
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 100.00
        ]);

        // Simulate order item creation (this would normally happen in order processing)
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => $product->price,
            'total_price' => $product->price * 3
        ]);

        // Update product stock
        $product->update(['stock_quantity' => $product->stock_quantity - 3]);

        // Assert
        $product->refresh();
        $this->assertEquals(7, $product->stock_quantity);
        $this->assertFalse($product->isLowStock()); // Above min_stock of 5

        // Create another order that brings it to low stock
        $order2 = Order::factory()->create(['user_id' => $user->id]);
        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => $product->price,
            'total_price' => $product->price * 3
        ]);

        $product->update(['stock_quantity' => $product->stock_quantity - 3]);

        // Assert low stock
        $product->refresh();
        $this->assertEquals(4, $product->stock_quantity);
        $this->assertTrue($product->isLowStock()); // Below min_stock of 5
    }

    /** @test */
    public function dashboard_shows_correct_category_product_counts_integration()
    {
        // Arrange
        $electronics = Category::factory()->create(['name' => 'Electronics']);
        $books = Category::factory()->create(['name' => 'Books']);
        $clothing = Category::factory()->create(['name' => 'Clothing']);

        // Create different numbers of products per category
        Product::factory()->count(5)->create(['category_id' => $electronics->id]);
        Product::factory()->count(3)->create(['category_id' => $books->id]);
        Product::factory()->count(8)->create(['category_id' => $clothing->id]);

        // Act
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Assert
        $response->assertOk();
        
        $categoriesWithCounts = $response->original->getData()['categoriesWithCounts'];
        
        $this->assertCount(3, $categoriesWithCounts);
        
        // Convert to array indexed by name for easier testing
        $categoryData = $categoriesWithCounts->keyBy('name');
        
        $this->assertEquals(5, $categoryData['Electronics']->products_count);
        $this->assertEquals(3, $categoryData['Books']->products_count);
        $this->assertEquals(8, $categoryData['Clothing']->products_count);
    }

    /** @test */
    public function product_scopes_work_with_real_database_integration()
    {
        // Arrange
        $activeCategory = Category::factory()->create(['active' => true]);
        $inactiveCategory = Category::factory()->create(['active' => false]);

        $activeProducts = Product::factory()->count(3)->create([
            'category_id' => $activeCategory->id,
            'active' => true,
            'stock_quantity' => 20
        ]);

        $inactiveProducts = Product::factory()->count(2)->create([
            'category_id' => $activeCategory->id,
            'active' => false,
            'stock_quantity' => 15
        ]);

        $lowStockProducts = Product::factory()->count(2)->create([
            'category_id' => $activeCategory->id,
            'active' => true,
            'stock_quantity' => 3 // Below default threshold of 10
        ]);

        $featuredProducts = Product::factory()->count(2)->create([
            'category_id' => $activeCategory->id,
            'active' => true,
            'promotional_price' => 50.00
        ]);

        // Act & Assert - Test various scopes
        
        // Test active scope
        $this->assertCount(7, Product::active()->get()); // 3 + 2 + 2 active products
        
        // Test lowStock scope
        $lowStock = Product::lowStock()->get();
        $this->assertCount(2, $lowStock);
        $this->assertTrue($lowStock->every(fn($p) => $p->stock_quantity <= 10));

        // Test featured scope
        $featured = Product::featured()->get();
        $this->assertCount(2, $featured);
        $this->assertTrue($featured->every(fn($p) => $p->promotional_price !== null));

        // Test forDashboard scope
        $dashboardProducts = Product::forDashboard()->get();
        $this->assertCount(9, $dashboardProducts); // All products
        $this->assertTrue($dashboardProducts->every(fn($p) => isset($p->category_name)));

        // Test dashboardStats scope
        $stats = Product::dashboardStats()->first();
        $this->assertEquals(9, $stats->total_products);
        $this->assertEquals(2, $stats->featured_count);
        $this->assertEquals(2, $stats->low_stock_count);
    }

    /** @test */
    public function product_update_affects_dashboard_data_integration()
    {
        // Arrange
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 15,
            'promotional_price' => null
        ]);

        // Verify initial state in dashboard
        $response1 = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $initialLowStock = $response1->original->getData()['lowStockProducts'];
        $this->assertCount(0, $initialLowStock); // Product has 15 stock, not low

        // Act - Update product to low stock
        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.products.update', $product), [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock_quantity' => 3, // Make it low stock
                'sku' => $product->sku,
                'category_id' => $category->id,
                'active' => true
            ]);

        // Assert update was successful
        $response->assertRedirect();
        
        // Clear cache to ensure fresh data
        $this->actingAs($this->adminUser)
            ->post(route('admin.dashboard.clear-cache'));

        // Verify dashboard shows updated data
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $updatedLowStock = $response2->original->getData()['lowStockProducts'];
        $this->assertCount(1, $updatedLowStock); // Now shows our updated product
        $this->assertEquals($product->id, $updatedLowStock->first()->id);
    }

    /** @test */
    public function category_product_relationship_queries_are_optimized_integration()
    {
        // Arrange
        $categories = Category::factory()->count(5)->create();
        
        foreach ($categories as $category) {
            Product::factory()->count(10)->create(['category_id' => $category->id]);
        }

        // Act - Monitor queries when loading dashboard
        DB::enableQueryLog();
        
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Assert
        $response->assertOk();

        // Verify we're using efficient queries with joins
        $joinQueries = array_filter($queries, function($query) {
            $sql = strtolower($query['query']);
            return str_contains($sql, 'join') && 
                   (str_contains($sql, 'categories') || str_contains($sql, 'products'));
        });

        $this->assertGreaterThan(0, count($joinQueries), 'Should use JOIN queries for category-product relationships');

        // Verify we don't have excessive queries (N+1 problem)
        $totalQueries = count($queries);
        $this->assertLessThan(25, $totalQueries, 'Should use optimized queries to avoid N+1 problem');
    }

    /** @test */
    public function bulk_product_operations_maintain_data_integrity_integration()
    {
        // Arrange
        $category = Category::factory()->create();
        $products = Product::factory()->count(10)->create([
            'category_id' => $category->id,
            'active' => true
        ]);

        // Act - Bulk update products (simulate admin bulk action)
        $productIds = $products->pluck('id')->toArray();
        
        // Bulk deactivate products
        Product::whereIn('id', $productIds)->update(['active' => false]);

        // Assert
        $this->assertEquals(0, Product::active()->count());
        $this->assertEquals(10, Product::where('active', false)->count());

        // Verify dashboard reflects the changes
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertOk();
        
        // All products should still exist in total count
        $this->assertEquals(10, $response->original->getData()['totalProducts']);
        
        // But none should show in active product searches
        $activeProductsCount = Product::active()->count();
        $this->assertEquals(0, $activeProductsCount);
    }
}
