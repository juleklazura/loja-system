<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    protected $category;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create(['active' => true]);
        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'promotional_price' => 80.00,
            'stock_quantity' => 10,
            'min_stock' => 2,
            'active' => true
        ]);
    }

    /** @test */
    public function it_belongs_to_a_category()
    {
        $this->assertInstanceOf(Category::class, $this->product->category);
        $this->assertEquals($this->category->id, $this->product->category->id);
    }

    /** @test */
    public function it_has_many_cart_items()
    {
        $user = User::factory()->create();
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $this->assertCount(1, $this->product->cartItems);
        $this->assertInstanceOf(CartItem::class, $this->product->cartItems->first());
    }

    /** @test */
    public function it_calculates_effective_price_with_promotional_price()
    {
        $this->assertEquals(80.00, $this->product->effective_price);
    }

    /** @test */
    public function it_calculates_effective_price_without_promotional_price()
    {
        $this->product->promotional_price = null;
        $this->product->save();

        $this->assertEquals(100.00, $this->product->effective_price);
    }

    /** @test */
    public function it_checks_if_product_is_in_stock()
    {
        $this->assertTrue($this->product->isInStock());
        
        $this->product->stock_quantity = 0;
        $this->product->save();
        
        $this->assertFalse($this->product->isInStock());
    }

    /** @test */
    public function it_checks_if_product_is_low_stock()
    {
        $this->assertFalse($this->product->isLowStock());
        
        $this->product->stock_quantity = 1; // Less than min_stock (2)
        $this->product->save();
        
        $this->assertTrue($this->product->isLowStock());
    }

    /** @test */
    public function it_scopes_active_products()
    {
        Product::factory()->create(['active' => false]);
        
        $activeProducts = Product::active()->get();
        
        $this->assertCount(1, $activeProducts);
        $this->assertTrue($activeProducts->first()->active);
    }

    /** @test */
    public function it_scopes_in_stock_products()
    {
        Product::factory()->create(['stock_quantity' => 0]);
        
        $inStockProducts = Product::inStock()->get();
        
        $this->assertCount(1, $inStockProducts);
        $this->assertGreaterThan(0, $inStockProducts->first()->stock_quantity);
    }

    /** @test */
    public function it_scopes_low_stock_products()
    {
        // Create product with low stock
        Product::factory()->create([
            'stock_quantity' => 1,
            'min_stock' => 5,
            'active' => true
        ]);
        
        $lowStockProducts = Product::lowStock()->get();
        
        $this->assertCount(1, $lowStockProducts);
        $this->assertLessThan(5, $lowStockProducts->first()->stock_quantity);
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $this->assertIsBool($this->product->active);
        $this->assertIsFloat($this->product->price);
        $this->assertIsFloat($this->product->promotional_price);
        
        // Test array casting for images
        $this->product->images = ['image1.jpg', 'image2.jpg'];
        $this->product->save();
        
        $this->product->refresh();
        $this->assertIsArray($this->product->images);
        $this->assertCount(2, $this->product->images);
    }

    /** @test */
    public function it_handles_null_promotional_price()
    {
        $this->product->promotional_price = null;
        $this->product->save();

        $this->assertEquals(100.00, $this->product->effective_price);
        $this->assertNull($this->product->promotional_price);
    }

    /** @test */
    public function it_generates_sku_when_creating()
    {
        $newProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'sku' => null // Let the factory or model generate it
        ]);

        $this->assertNotNull($newProduct->sku);
    }

    /** @test */
    public function it_filters_by_category_scope()
    {
        $otherCategory = Category::factory()->create(['active' => true]);
        Product::factory()->create(['category_id' => $otherCategory->id]);
        
        $productsInCategory = Product::whereCategory($this->category->id)->get();
        
        $this->assertCount(1, $productsInCategory);
        $this->assertEquals($this->category->id, $productsInCategory->first()->category_id);
    }

    /** @test */
    public function it_searches_by_name()
    {
        $this->product->update(['name' => 'Special Test Product']);
        Product::factory()->create(['name' => 'Different Product']);
        
        $searchResults = Product::where('name', 'like', '%Special%')->get();
        
        $this->assertCount(1, $searchResults);
        $this->assertStringContainsString('Special', $searchResults->first()->name);
    }

    /** @test */
    public function it_filters_by_price_range()
    {
        Product::factory()->create(['price' => 50.00]);
        Product::factory()->create(['price' => 200.00]);
        
        $midPriceProducts = Product::whereBetween('price', [75.00, 150.00])->get();
        
        $this->assertCount(1, $midPriceProducts);
        $this->assertEquals(100.00, $midPriceProducts->first()->price);
    }

    /** @test */
    public function it_has_image_accessor()
    {
        $this->product->images = ['image1.jpg', 'image2.jpg'];
        $this->product->save();

        $firstImage = $this->product->first_image;
        $this->assertEquals('image1.jpg', $firstImage);
    }

    /** @test */
    public function it_handles_empty_images_array()
    {
        $this->product->images = [];
        $this->product->save();

        $this->assertIsArray($this->product->images);
        $this->assertCount(0, $this->product->images);
    }
}
