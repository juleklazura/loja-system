<?php

namespace Tests\Unit\Factories;

use Tests\TestCase;
use App\Models\CartItem;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;

class FactoriesTestSimple extends TestCase
{
    /** @test */
    public function category_factory_works()
    {
        $category = Category::factory()->create();
        
        $this->assertInstanceOf(Category::class, $category);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /** @test */
    public function product_factory_works_with_category()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals($category->id, $product->category_id);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    /** @test */
    public function cart_item_factory_works()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        $cartItem = CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);
        
        $this->assertInstanceOf(CartItem::class, $cartItem);
        $this->assertEquals($user->id, $cartItem->user_id);
        $this->assertEquals($product->id, $cartItem->product_id);
        $this->assertDatabaseHas('cart_items', ['id' => $cartItem->id]);
    }

    /** @test */
    public function can_create_multiple_cart_items()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);
        
        $cartItem1 = CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 2
        ]);
        
        $cartItem2 = CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
            'quantity' => 3
        ]);
        
        $this->assertEquals(2, CartItem::where('user_id', $user->id)->count());
        $this->assertEquals(5, CartItem::where('user_id', $user->id)->sum('quantity'));
    }
}
