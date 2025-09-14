<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\CartItem;
use App\Http\Controllers\Frontend\CartController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'user_type' => 'customer'
        ]);

        // Create test product
        $category = Category::factory()->create(['active' => true]);
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'stock_quantity' => 10,
            'active' => true
        ]);

        $this->controller = new CartController();
    }

    /** @test */
    public function it_can_add_product_to_cart()
    {
        Auth::login($this->user);

        $request = Request::create('/cart/add', 'POST', [
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->controller->add($request);

        // Assert response
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Produto adicionado ao carrinho', $responseData['message']);
        $this->assertEquals(2, $responseData['cart_count']);

        // Assert database
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);
    }

    /** @test */
    public function it_cannot_add_out_of_stock_product()
    {
        Auth::login($this->user);

        $outOfStockProduct = Product::factory()->create([
            'category_id' => $this->product->category_id,
            'stock_quantity' => 0,
            'active' => true
        ]);

        $request = Request::create('/cart/add', 'POST', [
            'product_id' => $outOfStockProduct->id,
            'quantity' => 1
        ]);

        $response = $this->controller->add($request);

        $this->assertEquals(400, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Produto fora de estoque', $responseData['message']);
    }

    /** @test */
    public function it_cannot_add_quantity_exceeding_stock()
    {
        Auth::login($this->user);

        $request = Request::create('/cart/add', 'POST', [
            'product_id' => $this->product->id,
            'quantity' => 15 // More than the 10 in stock
        ]);

        $response = $this->controller->add($request);

        $this->assertEquals(400, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Quantidade indisponível', $responseData['message']);
    }

    /** @test */
    public function it_can_update_existing_cart_item()
    {
        Auth::login($this->user);

        // Add item first
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);

        $request = Request::create('/cart/add', 'POST', [
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->controller->add($request);

        $this->assertEquals(200, $response->getStatusCode());

        // Assert quantity is updated (1 + 2 = 3)
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 3
        ]);
    }

    /** @test */
    public function it_can_update_cart_item_quantity()
    {
        Auth::login($this->user);

        $cartItem = CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $request = Request::create('/cart/update', 'POST', [
            'item_id' => $cartItem->id,
            'quantity' => 5
        ]);

        $response = $this->controller->update($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Quantidade atualizada', $responseData['message']);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5
        ]);
    }

    /** @test */
    public function it_can_remove_cart_item()
    {
        Auth::login($this->user);

        $cartItem = CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $request = Request::create('/cart/remove', 'POST', [
            'item_id' => $cartItem->id
        ]);

        $response = $this->controller->remove($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Item removido do carrinho', $responseData['message']);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id
        ]);
    }

    /** @test */
    public function it_displays_cart_index_with_items()
    {
        Auth::login($this->user);

        // Add items to cart
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->controller->index();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('frontend.cart.index', $response->getName());

        $viewData = $response->getData();
        $this->assertArrayHasKey('cartItems', $viewData);
        $this->assertArrayHasKey('cartTotal', $viewData);
        $this->assertEquals(1, $viewData['cartItems']->count());
        $this->assertEquals(200.00, $viewData['cartTotal']); // 2 * 100.00
    }

    /** @test */
    public function it_validates_add_request()
    {
        Auth::login($this->user);

        $request = Request::create('/cart/add', 'POST', [
            'product_id' => 99999, // Non-existent product
            'quantity' => 0 // Invalid quantity
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->controller->add($request);
    }

    /** @test */
    public function it_validates_update_request()
    {
        Auth::login($this->user);

        $request = Request::create('/cart/update', 'POST', [
            'item_id' => 99999, // Non-existent item
            'quantity' => 0 // Invalid quantity
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->controller->update($request);
    }

    /** @test */
    public function it_prevents_unauthorized_access_to_cart_item()
    {
        Auth::login($this->user);

        // Create cart item for another user
        $otherUser = User::factory()->create();
        $cartItem = CartItem::create([
            'user_id' => $otherUser->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);

        $request = Request::create('/cart/update', 'POST', [
            'item_id' => $cartItem->id,
            'quantity' => 2
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->controller->update($request);
    }
}
