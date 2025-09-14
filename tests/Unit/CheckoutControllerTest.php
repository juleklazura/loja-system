<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Http\Controllers\Frontend\CheckoutController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product1;
    protected $product2;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'user_type' => 'customer'
        ]);

        // Create test products
        $category = Category::factory()->create(['active' => true]);
        $this->product1 = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'stock_quantity' => 10,
            'active' => true
        ]);

        $this->product2 = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 50.00,
            'stock_quantity' => 5,
            'active' => true
        ]);

        $this->controller = new CheckoutController();
    }

    /** @test */
    public function it_displays_checkout_page_with_cart_items()
    {
        Auth::login($this->user);

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

        $response = $this->controller->index();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('frontend.checkout.index', $response->getName());

        $viewData = $response->getData();
        $this->assertArrayHasKey('cartItems', $viewData);
        $this->assertArrayHasKey('cartTotal', $viewData);
        $this->assertEquals(2, $viewData['cartItems']->count());
        $this->assertEquals(250.00, $viewData['cartTotal']); // (2 * 100) + (1 * 50)
    }

    /** @test */
    public function it_redirects_when_cart_is_empty()
    {
        Auth::login($this->user);

        $response = $this->controller->index();

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('cart', $response->getTargetUrl());
    }

    /** @test */
    public function it_can_process_checkout_successfully()
    {
        Auth::login($this->user);

        // Add items to cart
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 2
        ]);

        $request = Request::create('/checkout', 'POST', [
            'shipping_address' => '123 Test Street, Test City, 12345-678',
            'billing_address' => '123 Test Street, Test City, 12345-678',
            'payment_method' => 'credit_card',
            'notes' => 'Test order notes'
        ]);

        $response = $this->controller->process($request);

        // Assert redirect to success page
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('sucesso', $response->getTargetUrl());

        // Assert order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'credit_card',
            'notes' => 'Test order notes'
        ]);

        // Assert order item was created
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product1->id,
            'quantity' => 2
        ]);

        // Assert cart was cleared
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->user->id
        ]);

        // Assert stock was decremented
        $this->product1->refresh();
        $this->assertEquals(8, $this->product1->stock_quantity); // 10 - 2
    }

    /** @test */
    public function it_validates_checkout_request()
    {
        Auth::login($this->user);

        // Add item to cart
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 1
        ]);

        $request = Request::create('/checkout', 'POST', [
            // Missing required fields
            'payment_method' => 'invalid_method'
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->controller->process($request);
    }

    /** @test */
    public function it_redirects_when_processing_empty_cart()
    {
        Auth::login($this->user);

        $request = Request::create('/checkout', 'POST', [
            'shipping_address' => '123 Test Street',
            'payment_method' => 'credit_card'
        ]);

        $response = $this->controller->process($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('cart', $response->getTargetUrl());
    }

    /** @test */
    public function it_displays_order_success_page()
    {
        Auth::login($this->user);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 150.00
        ]);

        $response = $this->controller->success($order);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('frontend.checkout.success', $response->getName());

        $viewData = $response->getData();
        $this->assertArrayHasKey('order', $viewData);
        $this->assertEquals($order->id, $viewData['order']->id);
    }

    /** @test */
    public function it_prevents_unauthorized_access_to_order_success()
    {
        Auth::login($this->user);

        // Create order for another user
        $otherUser = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $otherUser->id,
            'total_amount' => 150.00
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->controller->success($order);
    }

    /** @test */
    public function it_displays_user_orders()
    {
        Auth::login($this->user);

        // Create orders for user
        $order1 = Order::factory()->create(['user_id' => $this->user->id]);
        $order2 = Order::factory()->create(['user_id' => $this->user->id]);

        // Create order for another user (should not appear)
        $otherUser = User::factory()->create();
        Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->controller->orders();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('frontend.account.orders', $response->getName());

        $viewData = $response->getData();
        $this->assertArrayHasKey('orders', $viewData);
        $this->assertEquals(2, $viewData['orders']->count());
    }

    /** @test */
    public function it_displays_order_detail()
    {
        Auth::login($this->user);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 200.00
        ]);

        // Create order item
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
            'unit_price' => 100.00,
            'total_price' => 200.00
        ]);

        $response = $this->controller->orderDetail($order);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('frontend.account.order-detail', $response->getName());

        $viewData = $response->getData();
        $this->assertArrayHasKey('order', $viewData);
        $this->assertEquals($order->id, $viewData['order']->id);
        $this->assertEquals(1, $viewData['order']->orderItems->count());
    }

    /** @test */
    public function it_prevents_unauthorized_access_to_order_detail()
    {
        Auth::login($this->user);

        // Create order for another user
        $otherUser = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $otherUser->id,
            'total_amount' => 150.00
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->controller->orderDetail($order);
    }

    /** @test */
    public function it_calculates_order_total_correctly()
    {
        Auth::login($this->user);

        // Add items to cart with different quantities
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

        $request = Request::create('/checkout', 'POST', [
            'shipping_address' => '123 Test Street',
            'payment_method' => 'credit_card'
        ]);

        $this->controller->process($request);

        $order = Order::where('user_id', $this->user->id)->first();

        // Expected: (3 * 100) + (2 * 50) + shipping (10.00) = 410.00
        $this->assertEquals(410.00, $order->total_amount);
        $this->assertEquals(10.00, $order->shipping_amount);
    }

    /** @test */
    public function it_creates_order_items_with_correct_prices()
    {
        Auth::login($this->user);

        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 2
        ]);

        $request = Request::create('/checkout', 'POST', [
            'shipping_address' => '123 Test Street',
            'payment_method' => 'pix'
        ]);

        $this->controller->process($request);

        $order = Order::where('user_id', $this->user->id)->first();
        $orderItem = $order->orderItems->first();

        $this->assertEquals($this->product1->id, $orderItem->product_id);
        $this->assertEquals(2, $orderItem->quantity);
        $this->assertEquals($this->product1->effective_price, $orderItem->unit_price);
        $this->assertEquals($this->product1->effective_price * 2, $orderItem->total_price);
    }
}
