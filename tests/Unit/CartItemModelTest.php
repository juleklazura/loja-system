<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class CartItemModelTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $cartItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['user_type' => 'customer']);
        $category = Category::factory()->create(['active' => true]);
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'stock_quantity' => 10,
            'active' => true
        ]);

        $this->cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $this->assertInstanceOf(User::class, $this->cartItem->user);
        $this->assertEquals($this->user->id, $this->cartItem->user->id);
    }

    /** @test */
    public function it_belongs_to_a_product()
    {
        $this->assertInstanceOf(Product::class, $this->cartItem->product);
        $this->assertEquals($this->product->id, $this->cartItem->product->id);
    }

    /** @test */
    public function it_calculates_total_price()
    {
        $totalPrice = $this->cartItem->total_price;
        
        $this->assertEquals(200.00, $totalPrice); // 2 * 100.00
    }

    /** @test */
    public function it_gets_user_cart_quantity()
    {
        // Create additional cart item for the same user
        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 3
        ]);

        $totalQuantity = CartItem::getUserCartQuantity($this->user->id);
        
        $this->assertEquals(5, $totalQuantity); // 2 + 3
    }

    /** @test */
    public function it_gets_user_cart_count()
    {
        // Create additional cart item for the same user (different product)
        $otherProduct = Product::factory()->create([
            'category_id' => $this->product->category_id,
            'active' => true
        ]);
        
        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $otherProduct->id,
            'quantity' => 1
        ]);

        $cartCount = CartItem::getUserCartCount($this->user->id);
        
        $this->assertEquals(2, $cartCount); // 2 items
    }

    /** @test */
    public function it_caches_cart_quantity()
    {
        $cacheKey = "cart_quantity_{$this->user->id}";
        
        // Clear any existing cache
        Cache::forget($cacheKey);
        
        // First call should cache the result
        $quantity1 = CartItem::getUserCartQuantity($this->user->id);
        $this->assertTrue(Cache::has($cacheKey));
        
        // Second call should return cached result
        $quantity2 = CartItem::getUserCartQuantity($this->user->id);
        $this->assertEquals($quantity1, $quantity2);
    }

    /** @test */
    public function it_clears_cache_when_cart_item_is_saved()
    {
        $cacheKey = "cart_quantity_{$this->user->id}";
        
        // Cache the quantity
        CartItem::getUserCartQuantity($this->user->id);
        $this->assertTrue(Cache::has($cacheKey));
        
        // Update cart item should clear cache
        $this->cartItem->quantity = 5;
        $this->cartItem->save();
        
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function it_clears_cache_when_cart_item_is_deleted()
    {
        $cacheKey = "cart_quantity_{$this->user->id}";
        
        // Cache the quantity
        CartItem::getUserCartQuantity($this->user->id);
        $this->assertTrue(Cache::has($cacheKey));
        
        // Delete cart item should clear cache
        $this->cartItem->delete();
        
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function it_loads_cart_with_products_optimized()
    {
        $cartItems = CartItem::withProductsOptimized($this->user->id)->get();
        
        $this->assertCount(1, $cartItems);
        $this->assertTrue($cartItems->first()->relationLoaded('product'));
        $this->assertTrue($cartItems->first()->product->relationLoaded('category'));
    }

    /** @test */
    public function it_handles_multiple_cart_items_for_same_user()
    {
        // Create additional products and cart items
        $product2 = Product::factory()->create([
            'category_id' => $this->product->category_id,
            'price' => 50.00,
            'active' => true
        ]);
        
        $product3 = Product::factory()->create([
            'category_id' => $this->product->category_id,
            'price' => 75.00,
            'active' => true
        ]);

        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product2->id,
            'quantity' => 1
        ]);

        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product3->id,
            'quantity' => 3
        ]);

        $totalQuantity = CartItem::getUserCartQuantity($this->user->id);
        $totalCount = CartItem::getUserCartCount($this->user->id);
        
        $this->assertEquals(6, $totalQuantity); // 2 + 1 + 3
        $this->assertEquals(3, $totalCount); // 3 different items
    }

    /** @test */
    public function it_isolates_cart_data_by_user()
    {
        $otherUser = User::factory()->create(['user_type' => 'customer']);
        
        // Create cart items for other user
        CartItem::factory()->create([
            'user_id' => $otherUser->id,
            'product_id' => $this->product->id,
            'quantity' => 5
        ]);

        // Original user should still have only their items
        $userQuantity = CartItem::getUserCartQuantity($this->user->id);
        $otherUserQuantity = CartItem::getUserCartQuantity($otherUser->id);
        
        $this->assertEquals(2, $userQuantity);
        $this->assertEquals(5, $otherUserQuantity);
    }

    /** @test */
    public function it_returns_zero_for_empty_cart()
    {
        $emptyUser = User::factory()->create(['user_type' => 'customer']);
        
        $quantity = CartItem::getUserCartQuantity($emptyUser->id);
        $count = CartItem::getUserCartCount($emptyUser->id);
        
        $this->assertEquals(0, $quantity);
        $this->assertEquals(0, $count);
    }

    /** @test */
    public function it_clears_all_cache_keys_for_user()
    {
        $userId = $this->user->id;
        
        // Cache multiple values
        CartItem::getUserCartQuantity($userId);
        CartItem::getUserCartCount($userId);
        
        $this->assertTrue(Cache::has("cart_quantity_{$userId}"));
        $this->assertTrue(Cache::has("cart_count_{$userId}"));
        
        // Clear cache
        CartItem::clearUserCartCache($userId);
        
        $this->assertFalse(Cache::has("cart_quantity_{$userId}"));
        $this->assertFalse(Cache::has("cart_count_{$userId}"));
        $this->assertFalse(Cache::has("cart_items_{$userId}"));
    }

    /** @test */
    public function it_updates_quantity_correctly()
    {
        $this->assertEquals(2, $this->cartItem->quantity);
        
        $this->cartItem->quantity = 5;
        $this->cartItem->save();
        
        $this->cartItem->refresh();
        $this->assertEquals(5, $this->cartItem->quantity);
    }

    /** @test */
    public function it_validates_fillable_attributes()
    {
        $fillable = $this->cartItem->getFillable();
        
        $this->assertContains('user_id', $fillable);
        $this->assertContains('product_id', $fillable);
        $this->assertContains('quantity', $fillable);
    }
}
