<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductScopesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function scope_for_dashboard_returns_optimized_fields()
    {
        // Arrange
        $category = Category::factory()->create(['name' => 'Electronics']);
        $product = Product::factory()->create([
            'name' => 'Smartphone',
            'price' => 699.99,
            'category_id' => $category->id,
            'stock_quantity' => 50
        ]);

        // Act
        $result = Product::forDashboard()->first();

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($product->id, $result->id);
        $this->assertEquals('Smartphone', $result->name);
        $this->assertEquals(699.99, $result->price);
        $this->assertEquals(50, $result->stock_quantity);
        $this->assertEquals('Electronics', $result->category_name);
        
        // Verify it has the expected fields from the join
        $this->assertTrue(isset($result->category_name));
    }

    /** @test */
    public function scope_for_dashboard_orders_by_latest()
    {
        // Arrange
        $category = Category::factory()->create();
        
        $oldProduct = Product::factory()->create([
            'category_id' => $category->id,
            'created_at' => now()->subDays(2)
        ]);
        
        $newProduct = Product::factory()->create([
            'category_id' => $category->id,
            'created_at' => now()->subDay()
        ]);

        // Act
        $results = Product::forDashboard()->get();

        // Assert
        $this->assertCount(2, $results);
        $this->assertEquals($newProduct->id, $results->first()->id); // Most recent first
        $this->assertEquals($oldProduct->id, $results->last()->id);
    }

    /** @test */
    public function scope_featured_returns_only_featured_products()
    {
        // Arrange
        $category = Category::factory()->create();
        
        $featuredProduct = Product::factory()->create([
            'category_id' => $category->id,
            'promotional_price' => 50.00 // Has promotional price = featured
        ]);
        
        $normalProduct = Product::factory()->create([
            'category_id' => $category->id,
            'promotional_price' => null // No promotional price = not featured
        ]);

        // Act
        $results = Product::featured()->get();

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals($featuredProduct->id, $results->first()->id);
        $this->assertNotNull($results->first()->promotional_price);
    }

    /** @test */
    public function scope_low_stock_returns_products_below_threshold()
    {
        // Arrange
        $category = Category::factory()->create();
        
        $lowStockProduct1 = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 5
        ]);
        
        $lowStockProduct2 = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 0
        ]);
        
        $normalStockProduct = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 20
        ]);

        // Act - Default threshold is 10
        $results = Product::lowStock()->get();

        // Assert
        $this->assertCount(2, $results);
        $stockQuantities = $results->pluck('stock_quantity')->toArray();
        $this->assertContains(5, $stockQuantities);
        $this->assertContains(0, $stockQuantities);
        $this->assertNotContains(20, $stockQuantities);
    }

    /** @test */
    public function scope_low_stock_accepts_custom_threshold()
    {
        // Arrange
        $category = Category::factory()->create();
        
        Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 5
        ]);
        
        Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 15
        ]);
        
        Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 25
        ]);

        // Act - Custom threshold of 20
        $results = Product::lowStock(20)->get();

        // Assert
        $this->assertCount(2, $results);
        $stockQuantities = $results->pluck('stock_quantity')->toArray();
        $this->assertContains(5, $stockQuantities);
        $this->assertContains(15, $stockQuantities);
        $this->assertNotContains(25, $stockQuantities);
    }

    /** @test */
    public function scope_dashboard_stats_calculates_totals()
    {
        // Arrange
        $category = Category::factory()->create();
        
        Product::factory()->count(5)->create([
            'category_id' => $category->id,
            'stock_quantity' => 10
        ]);
        
        Product::factory()->count(3)->create([
            'category_id' => $category->id,
            'stock_quantity' => 20
        ]);
        
        Product::factory()->count(2)->create([
            'category_id' => $category->id,
            'stock_quantity' => 5,
            'promotional_price' => 50.00 // Featured products
        ]);

        // Act
        $stats = Product::dashboardStats()->first();

        // Assert
        $this->assertEquals(10, $stats->total_products);
        $this->assertEquals(110, $stats->total_stock); // (5 * 10) + (3 * 20) + (2 * 5)
        $this->assertEquals(2, $stats->featured_count);
        $this->assertEquals(2, $stats->low_stock_count); // Products with stock <= 10 (default threshold)
    }

    /** @test */
    public function scope_dashboard_stats_handles_empty_data()
    {
        // Act - No products in database
        $stats = Product::dashboardStats()->first();

        // Assert
        $this->assertEquals(0, $stats->total_products);
        $this->assertEquals(0, $stats->total_stock);
        $this->assertEquals(0, $stats->featured_count);
        $this->assertEquals(0, $stats->low_stock_count);
    }

    /** @test */
    public function scope_dashboard_stats_uses_correct_low_stock_threshold()
    {
        // Arrange
        $category = Category::factory()->create();
        
        Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 5
        ]);
        
        Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10
        ]);
        
        Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 15
        ]);

        // Act
        $stats = Product::dashboardStats()->first();

        // Assert
        $this->assertEquals(3, $stats->total_products);
        $this->assertEquals(2, $stats->low_stock_count); // Stock <= 10
    }

    /** @test */
    public function scope_for_dashboard_includes_correct_join()
    {
        // Arrange
        $category1 = Category::factory()->create(['name' => 'Electronics']);
        $category2 = Category::factory()->create(['name' => 'Books']);
        
        Product::factory()->create(['category_id' => $category1->id]);
        Product::factory()->create(['category_id' => $category2->id]);

        // Act
        $results = Product::forDashboard()->get();

        // Assert
        $this->assertCount(2, $results);
        
        $categoryNames = $results->pluck('category_name')->toArray();
        $this->assertContains('Electronics', $categoryNames);
        $this->assertContains('Books', $categoryNames);
    }

    /** @test */
    public function scope_featured_works_with_other_scopes()
    {
        // Arrange
        $category = Category::factory()->create();
        
        Product::factory()->create([
            'category_id' => $category->id,
            'promotional_price' => 50.00, // Featured
            'stock_quantity' => 5
        ]);
        
        Product::factory()->create([
            'category_id' => $category->id,
            'promotional_price' => 75.00, // Featured
            'stock_quantity' => 15
        ]);
        
        Product::factory()->create([
            'category_id' => $category->id,
            'promotional_price' => null, // Not featured
            'stock_quantity' => 5
        ]);

        // Act
        $results = Product::featured()->lowStock()->get();

        // Assert
        $this->assertCount(1, $results);
        $this->assertNotNull($results->first()->promotional_price);
        $this->assertEquals(5, $results->first()->stock_quantity);
    }

    /** @test */
    public function scopes_can_be_combined_with_query_methods()
    {
        // Arrange
        $category = Category::factory()->create();
        Product::factory()->count(10)->create([
            'category_id' => $category->id,
            'promotional_price' => 50.00 // Featured products
        ]);

        // Act
        $limitedResults = Product::featured()->limit(5)->get();
        $countResult = Product::featured()->count();

        // Assert
        $this->assertCount(5, $limitedResults);
        $this->assertEquals(10, $countResult);
    }

    /** @test */
    public function scope_low_stock_orders_by_stock_quantity_asc()
    {
        // Arrange
        $category = Category::factory()->create();
        
        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 8
        ]);
        
        $product2 = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 3
        ]);
        
        $product3 = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 0
        ]);

        // Act
        $results = Product::lowStock()->get();

        // Assert
        $this->assertCount(3, $results);
        
        // Verify ordering (lowest stock first)
        $stockQuantities = $results->pluck('stock_quantity')->toArray();
        $this->assertEquals([0, 3, 8], $stockQuantities);
    }
}
