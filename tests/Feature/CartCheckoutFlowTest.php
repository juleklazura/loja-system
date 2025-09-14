<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class CartCheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product1;
    protected $product2;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_type' => 'customer',
            'email_verified_at' => now()
        ]);

        $this->category = Category::factory()->create(['active' => true]);

        $this->product1 = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Test Product 1',
            'price' => 100.00,
            'stock_quantity' => 10,
            'active' => true
        ]);

        $this->product2 = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Test Product 2',
            'price' => 50.00,
            'stock_quantity' => 5,
            'active' => true
        ]);
    }

    /** @test */
    public function user_can_view_empty_cart()
    {
        $response = $this->actingAs($this->user)
                        ->get(route('cart.index'));

        $response->assertOk()
                ->assertViewIs('frontend.cart.index')
                ->assertSee('Seu carrinho está vazio');
    }

    /** @test */
    public function user_can_add_product_to_cart()
    {
        $response = $this->actingAs($this->user)
                        ->postJson(route('cart.add'), [
                            'product_id' => $this->product1->id,
                            'quantity' => 2
                        ]);

        $response->assertOk()
                ->assertJson([
                    'success' => true,
                    'message' => 'Produto adicionado ao carrinho',
                    'cart_count' => 2
                ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 2
        ]);
    }

    /** @test */
    public function user_can_view_cart_with_items()
    {
        // Add items to cart
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 2
        ]);

        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product2->id,
            'quantity' => 1
        ]);

        $response = $this->actingAs($this->user)
                        ->get(route('cart.index'));

        $response->assertOk()
                ->assertViewIs('frontend.cart.index')
                ->assertViewHas('cartItems')
                ->assertViewHas('cartTotal')
                ->assertSee($this->product1->name)
                ->assertSee($this->product2->name);

        $cartTotal = $response->viewData('cartTotal');
        $this->assertEquals(250.00, $cartTotal); // (2 * 100) + (1 * 50)
    }

    /** @test */
    public function user_can_update_cart_item_quantity()
    {
        $cartItem = CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)
                        ->postJson(route('cart.update'), [
                            'item_id' => $cartItem->id,
                            'quantity' => 5
                        ]);

        $response->assertOk()
                ->assertJson([
                    'success' => true,
                    'message' => 'Quantidade atualizada',
                    'cart_count' => 5
                ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5
        ]);
    }

    /** @test */
    public function user_can_remove_item_from_cart()
    {
        $cartItem = CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)
                        ->postJson(route('cart.remove'), [
                            'item_id' => $cartItem->id
                        ]);

        $response->assertOk()
                ->assertJson([
                    'success' => true,
                    'message' => 'Item removido do carrinho',
                    'cart_count' => 0
                ]);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id
        ]);
    }

    /** @test */
    public function user_can_view_checkout_page()
    {
        // Add items to cart
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 1
        ]);

        $response = $this->actingAs($this->user)
                        ->get(route('checkout.index'));

        $response->assertOk()
                ->assertViewIs('frontend.checkout.index')
                ->assertViewHas('cartItems')
                ->assertViewHas('cartTotal')
                ->assertSee($this->product1->name);
    }

    /** @test */
    public function user_cannot_checkout_with_empty_cart()
    {
        $response = $this->actingAs($this->user)
                        ->get(route('checkout.index'));

        $response->assertRedirect(route('cart.index'))
                ->assertSessionHas('error', 'Seu carrinho está vazio');
    }

    /** @test */
    public function user_can_complete_checkout()
    {
        // Add items to cart
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 2
        ]);

        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product2->id,
            'quantity' => 1
        ]);

        $checkoutData = [
            'shipping_address' => '123 Test Street, Test City, 12345-678',
            'billing_address' => '123 Test Street, Test City, 12345-678',
            'payment_method' => 'credit_card',
            'notes' => 'Test checkout order'
        ];

        $response = $this->actingAs($this->user)
                        ->post(route('checkout.process'), $checkoutData);

        $response->assertRedirect();
        $this->assertStringContainsString('sucesso', $response->getTargetUrl());

        // Assert order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'credit_card',
            'notes' => 'Test checkout order'
        ]);

        $order = Order::where('user_id', $this->user->id)->first();

        // Assert order items were created
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product1->id,
            'quantity' => 2
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product2->id,
            'quantity' => 1
        ]);

        // Assert cart was cleared
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->user->id
        ]);

        // Assert stock was decremented
        $this->product1->refresh();
        $this->product2->refresh();
        
        $this->assertEquals(8, $this->product1->stock_quantity); // 10 - 2
        $this->assertEquals(4, $this->product2->stock_quantity); // 5 - 1
    }

    /** @test */
    public function checkout_calculates_total_correctly()
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 3
        ]);

        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product2->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)
                        ->post(route('checkout.process'), [
                            'shipping_address' => '123 Test Street',
                            'payment_method' => 'pix'
                        ]);

        $order = Order::where('user_id', $this->user->id)->first();

        // Expected: (3 * 100) + (2 * 50) + shipping (10) = 410.00
        $this->assertEquals(410.00, $order->total_amount);
        $this->assertEquals(10.00, $order->shipping_amount);
    }

    /** @test */
    public function user_can_view_order_success_page()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 150.00
        ]);

        $response = $this->actingAs($this->user)
                        ->get(route('checkout.success', $order));

        $response->assertOk()
                ->assertViewIs('frontend.checkout.success')
                ->assertViewHas('order');
    }

    /** @test */
    public function user_cannot_view_other_users_order_success()
    {
        $otherUser = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $otherUser->id,
            'total_amount' => 150.00
        ]);

        $response = $this->actingAs($this->user)
                        ->get(route('checkout.success', $order));

        $response->assertForbidden();
    }

    /** @test */
    public function user_cannot_add_out_of_stock_product()
    {
        $outOfStockProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 0,
            'active' => true
        ]);

        $response = $this->actingAs($this->user)
                        ->postJson(route('cart.add'), [
                            'product_id' => $outOfStockProduct->id,
                            'quantity' => 1
                        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Produto fora de estoque'
                ]);
    }

    /** @test */
    public function user_cannot_add_more_than_available_stock()
    {
        $response = $this->actingAs($this->user)
                        ->postJson(route('cart.add'), [
                            'product_id' => $this->product2->id,
                            'quantity' => 10 // More than the 5 in stock
                        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Quantidade indisponível'
                ]);
    }

    /** @test */
    public function cart_preserves_items_across_sessions()
    {
        // Add item to cart
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 2
        ]);

        // Logout and login again
        Auth::logout();
        
        $response = $this->actingAs($this->user)
                        ->get(route('cart.index'));

        $response->assertOk()
                ->assertSee($this->product1->name);

        // Item should still be in database
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 2
        ]);
    }

    /** @test */
    public function guest_user_is_redirected_to_login_for_cart_operations()
    {
        $response = $this->get(route('cart.index'));
        $response->assertRedirect(route('login'));

        $response = $this->postJson(route('cart.add'), [
            'product_id' => $this->product1->id,
            'quantity' => 1
        ]);
        $response->assertUnauthorized();
    }
}
