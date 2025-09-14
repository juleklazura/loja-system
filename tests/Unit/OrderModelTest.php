<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $order;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['user_type' => 'customer']);
        $category = Category::factory()->create(['active' => true]);
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'active' => true
        ]);

        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 250.00,
            'discount_amount' => 25.00,
            'shipping_amount' => 15.00,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $this->assertInstanceOf(User::class, $this->order->user);
        $this->assertEquals($this->user->id, $this->order->user->id);
    }

    /** @test */
    public function it_has_many_order_items()
    {
        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 100.00,
            'total_price' => 200.00
        ]);

        $this->assertCount(1, $this->order->orderItems);
        $this->assertInstanceOf(OrderItem::class, $this->order->orderItems->first());
    }

    /** @test */
    public function it_has_items_alias_for_order_items()
    {
        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 100.00,
            'total_price' => 100.00
        ]);

        $this->assertCount(1, $this->order->items);
        $this->assertEquals($this->order->orderItems->count(), $this->order->items->count());
    }

    /** @test */
    public function it_generates_unique_order_number_when_creating()
    {
        $newOrder = Order::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertNotNull($newOrder->order_number);
        $this->assertStringStartsWith('ORD-' . date('Y') . '-', $newOrder->order_number);
        $this->assertNotEquals($this->order->order_number, $newOrder->order_number);
    }

    /** @test */
    public function it_calculates_subtotal()
    {
        // Create order items
        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 100.00,
            'total_price' => 200.00
        ]);

        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 50.00,
            'total_price' => 50.00
        ]);

        $this->order->refresh();
        $this->assertEquals(250.00, $this->order->subtotal);
    }

    /** @test */
    public function it_calculates_final_amount()
    {
        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 100.00,
            'total_price' => 200.00
        ]);

        $this->order->refresh();
        
        // Final amount = subtotal - discount + shipping
        // = 200.00 - 25.00 + 15.00 = 190.00
        $this->assertEquals(190.00, $this->order->final_amount);
    }

    /** @test */
    public function it_casts_decimal_amounts_correctly()
    {
        $this->assertIsFloat($this->order->total_amount);
        $this->assertIsFloat($this->order->discount_amount);
        $this->assertIsFloat($this->order->shipping_amount);
        
        $this->assertEquals(250.00, $this->order->total_amount);
        $this->assertEquals(25.00, $this->order->discount_amount);
        $this->assertEquals(15.00, $this->order->shipping_amount);
    }

    /** @test */
    public function it_scopes_for_dashboard()
    {
        $dashboardOrders = Order::forDashboard()->get();
        
        $this->assertCount(1, $dashboardOrders);
        
        $dashboardOrder = $dashboardOrders->first();
        $this->assertEquals($this->order->id, $dashboardOrder->id);
        $this->assertEquals($this->order->total_amount, $dashboardOrder->total);
        $this->assertEquals($this->user->name, $dashboardOrder->user_name);
    }

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $fillable = $this->order->getFillable();
        
        $expectedFillable = [
            'order_number',
            'user_id',
            'status',
            'total_amount',
            'discount_amount',
            'shipping_amount',
            'shipping_address',
            'billing_address',
            'payment_method',
            'payment_status',
            'notes',
        ];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    /** @test */
    public function it_handles_different_order_statuses()
    {
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        foreach ($statuses as $status) {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => $status
            ]);
            
            $this->assertEquals($status, $order->status);
        }
    }

    /** @test */
    public function it_handles_different_payment_methods()
    {
        $paymentMethods = ['credit_card', 'debit_card', 'pix', 'boleto'];
        
        foreach ($paymentMethods as $method) {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'payment_method' => $method
            ]);
            
            $this->assertEquals($method, $order->payment_method);
        }
    }

    /** @test */
    public function it_handles_null_discount_and_shipping()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 100.00,
            'discount_amount' => null,
            'shipping_amount' => null
        ]);

        $this->assertNull($order->discount_amount);
        $this->assertNull($order->shipping_amount);
    }

    /** @test */
    public function it_can_have_billing_and_shipping_addresses()
    {
        $shippingAddress = '123 Shipping Street, City, 12345-678';
        $billingAddress = '456 Billing Avenue, City, 98765-432';
        
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address' => $shippingAddress,
            'billing_address' => $billingAddress
        ]);

        $this->assertEquals($shippingAddress, $order->shipping_address);
        $this->assertEquals($billingAddress, $order->billing_address);
    }

    /** @test */
    public function it_stores_order_notes()
    {
        $notes = 'Special delivery instructions';
        
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'notes' => $notes
        ]);

        $this->assertEquals($notes, $order->notes);
    }

    /** @test */
    public function it_has_timestamps()
    {
        $this->assertNotNull($this->order->created_at);
        $this->assertNotNull($this->order->updated_at);
    }

    /** @test */
    public function it_can_be_filtered_by_status()
    {
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'processing'
        ]);

        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'delivered'
        ]);

        $pendingOrders = Order::where('status', 'pending')->get();
        $processingOrders = Order::where('status', 'processing')->get();
        
        $this->assertCount(1, $pendingOrders);
        $this->assertCount(1, $processingOrders);
    }

    /** @test */
    public function it_can_be_filtered_by_date_range()
    {
        $todayOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()
        ]);

        $lastWeekOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subWeek()
        ]);

        $recentOrders = Order::where('created_at', '>=', now()->subDays(3))->get();
        
        $this->assertGreaterThanOrEqual(2, $recentOrders->count()); // At least the original order and today's order
    }
}
