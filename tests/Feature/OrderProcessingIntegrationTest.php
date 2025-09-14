<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Carbon\Carbon;

class OrderProcessingIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $adminUser;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true
        ]);

        $this->customer = User::factory()->create([
            'email' => 'customer@test.com',
            'is_admin' => false
        ]);
    }

    /** @test */
    public function complete_order_workflow_integration()
    {
        // Arrange
        $category = Category::factory()->create();
        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'stock_quantity' => 10
        ]);
        
        $product2 = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 50.00,
            'stock_quantity' => 5
        ]);

        // Act - Create order with multiple items
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total_amount' => 250.00, // (100*2) + (50*1)
            'shipping_address' => $this->faker->address(),
            'payment_method' => 'credit_card',
            'payment_status' => 'pending'
        ]);

        // Create order items
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => 100.00,
            'total_price' => 200.00
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => 50.00,
            'total_price' => 50.00
        ]);

        // Assert - Verify order structure
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total_amount' => 250.00
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 1
        ]);

        // Verify relationships work
        $order->refresh();
        $this->assertCount(2, $order->orderItems);
        $this->assertEquals($this->customer->id, $order->user->id);
        $this->assertEquals(250.00, $order->orderItems->sum('total_price'));
    }

    /** @test */
    public function order_status_updates_affect_dashboard_integration()
    {
        // Arrange
        $orders = collect();
        
        // Create orders with different statuses
        $orders->push(Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total_amount' => 100.00
        ]));

        $orders->push(Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'processing',
            'total_amount' => 150.00
        ]));

        $orders->push(Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'delivered',
            'total_amount' => 200.00
        ]));

        // Get initial dashboard data
        $response1 = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $initialData = $response1->original->getData();
        $this->assertEquals(3, $initialData['totalOrders']);

        // Act - Update order status
        $pendingOrder = $orders->where('status', 'pending')->first();
        
        $updateResponse = $this->actingAs($this->adminUser)
            ->put(route('admin.orders.update', $pendingOrder), [
                'status' => 'delivered'
            ]);

        // Clear cache to ensure fresh data
        Cache::flush();

        // Get updated dashboard data
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Assert
        $updateResponse->assertRedirect();
        $response2->assertOk();

        // Verify order was updated in database
        $this->assertDatabaseHas('orders', [
            'id' => $pendingOrder->id,
            'status' => 'delivered'
        ]);

        // Dashboard should still show same total orders but different status distribution
        $updatedData = $response2->original->getData();
        $this->assertEquals(3, $updatedData['totalOrders']);

        // Verify recent orders reflect the status change
        $recentOrders = $updatedData['recentOrders'];
        $updatedOrder = $recentOrders->where('id', $pendingOrder->id)->first();
        $this->assertEquals('delivered', $updatedOrder->status);
    }

    /** @test */
    public function order_revenue_calculations_integration()
    {
        // Arrange - Create orders across different dates
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        // Orders within the last 30 days
        $order1 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'total_amount' => 150.00,
            'created_at' => Carbon::now()->subDays(5)
        ]);

        $order2 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'total_amount' => 200.00,
            'created_at' => Carbon::now()->subDays(10)
        ]);

        $order3 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'total_amount' => 100.00,
            'created_at' => Carbon::now()->subDays(5) // Same date as order1
        ]);

        // Order outside the range (should not be included)
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'total_amount' => 500.00,
            'created_at' => Carbon::now()->subDays(40)
        ]);

        // Act - Get dashboard data
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Assert
        $response->assertOk();
        
        $revenueData = $response->original->getData()['revenueData'];
        
        // Should have 30 data points (one for each day)
        $this->assertCount(30, $revenueData['labels']);
        $this->assertCount(30, $revenueData['data']);

        // Total revenue for the period should be 450.00 (150 + 200 + 100)
        $totalRevenue = array_sum($revenueData['data']);
        $this->assertEquals(450.00, $totalRevenue);

        // Verify revenue aggregation by date works correctly
        $revenueByDate = array_combine($revenueData['labels'], $revenueData['data']);
        
        // Date with order1 and order3 should have 250.00 (150 + 100)
        $date5DaysAgo = Carbon::now()->subDays(5)->format('Y-m-d');
        $this->assertEquals(250.00, $revenueByDate[$date5DaysAgo]);

        // Date with order2 should have 200.00
        $date10DaysAgo = Carbon::now()->subDays(10)->format('Y-m-d');
        $this->assertEquals(200.00, $revenueByDate[$date10DaysAgo]);
    }

    /** @test */
    public function order_with_customer_relationship_integration()
    {
        // Arrange
        $customers = User::factory()->count(3)->create(['is_admin' => false]);
        $orders = collect();

        foreach ($customers as $customer) {
            $customerOrders = Order::factory()->count(2)->create([
                'user_id' => $customer->id,
                'total_amount' => $this->faker->randomFloat(2, 50, 300)
            ]);
            $orders = $orders->merge($customerOrders);
        }

        // Act - View admin orders index
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders.index'));

        // Assert
        $response->assertOk();

        // Get orders data from response
        $ordersData = $response->original->getData()['orders'] ?? collect();
        
        // Verify all orders are loaded with customer relationships
        $this->assertCount(6, $ordersData); // 3 customers * 2 orders each

        foreach ($ordersData as $order) {
            $this->assertNotNull($order->user);
            $this->assertNotNull($order->user->name);
            $this->assertFalse($order->user->is_admin);
        }

        // Verify dashboard shows correct recent orders with customer names
        $dashboardResponse = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $recentOrders = $dashboardResponse->original->getData()['recentOrders'];
        
        foreach ($recentOrders as $order) {
            $this->assertNotNull($order->user_name);
            $this->assertTrue(is_string($order->user_name));
        }
    }

    /** @test */
    public function order_scopes_work_with_dashboard_integration()
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create orders for revenue testing
        Order::factory()->create([
            'user_id' => $user1->id,
            'total_amount' => 100.00,
            'created_at' => Carbon::now()->subDays(5)
        ]);

        Order::factory()->create([
            'user_id' => $user2->id,
            'total_amount' => 150.00,
            'created_at' => Carbon::now()->subDays(3)
        ]);

        Order::factory()->create([
            'user_id' => $user1->id,
            'total_amount' => 200.00,
            'created_at' => Carbon::now()->subDays(1)
        ]);

        // Act - Test order scopes directly
        $dashboardOrders = Order::forDashboard()->get();
        $this->assertCount(3, $dashboardOrders);
        
        // Verify orders have user_name from join
        foreach ($dashboardOrders as $order) {
            $this->assertNotNull($order->user_name);
            $this->assertNotNull($order->total);
        }

        // Test revenue between dates scope
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();
        
        $revenueData = Order::revenueBetweenDates($startDate, $endDate)->get();
        
        // Should have 3 different dates
        $this->assertCount(3, $revenueData);
        
        // Verify revenue totals by date
        $totalRevenue = $revenueData->sum('total');
        $this->assertEquals(450.00, $totalRevenue);

        // Test dashboard stats scope
        $stats = Order::dashboardStats()->first();
        $this->assertEquals(3, $stats->total_orders);
        $this->assertEquals(450.00, $stats->total_revenue);
    }

    /** @test */
    public function order_deletion_maintains_data_integrity_integration()
    {
        // Arrange
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'total_amount' => 100.00
        ]);

        // Create order item
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100.00,
            'total_price' => 100.00
        ]);

        // Verify initial state
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
        $this->assertDatabaseHas('order_items', ['id' => $orderItem->id]);

        // Act - Delete order
        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.orders.destroy', $order));

        // Assert
        $response->assertRedirect();

        // Verify order is deleted
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        
        // Verify order items are also deleted (cascade or manual cleanup)
        $this->assertDatabaseMissing('order_items', ['order_id' => $order->id]);

        // Verify product still exists (should not be affected)
        $this->assertDatabaseHas('products', ['id' => $product->id]);

        // Verify customer still exists
        $this->assertDatabaseHas('users', ['id' => $this->customer->id]);
    }

    /** @test */
    public function multiple_orders_same_day_revenue_aggregation_integration()
    {
        // Arrange
        $user = User::factory()->create();
        $targetDate = Carbon::now()->subDays(3);

        // Create multiple orders on the same date
        Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 100.00,
            'created_at' => $targetDate->copy()->setTime(9, 0)
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 150.00,
            'created_at' => $targetDate->copy()->setTime(14, 30)
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 50.00,
            'created_at' => $targetDate->copy()->setTime(18, 45)
        ]);

        // Act - Get dashboard revenue data
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        // Assert
        $response->assertOk();
        
        $revenueData = $response->original->getData()['revenueData'];
        $revenueByDate = array_combine($revenueData['labels'], $revenueData['data']);

        // Revenue for the target date should be aggregated (100 + 150 + 50 = 300)
        $targetDateString = $targetDate->format('Y-m-d');
        $this->assertEquals(300.00, $revenueByDate[$targetDateString]);

        // Total revenue should include all orders
        $totalRevenue = array_sum($revenueData['data']);
        $this->assertEquals(300.00, $totalRevenue);
    }

    /** @test */
    public function order_performance_with_large_dataset_integration()
    {
        // Arrange - Create large dataset
        $users = User::factory()->count(20)->create();
        $orders = collect();

        foreach ($users as $user) {
            $userOrders = Order::factory()->count(rand(5, 15))->create([
                'user_id' => $user->id,
                'total_amount' => $this->faker->randomFloat(2, 25, 500),
                'created_at' => $this->faker->dateTimeBetween('-30 days', 'now')
            ]);
            $orders = $orders->merge($userOrders);
        }

        $this->assertGreaterThan(100, $orders->count());

        // Act - Measure dashboard load time
        $startTime = microtime(true);
        
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        // Assert
        $response->assertOk();
        
        // Should load reasonably fast even with large dataset
        $this->assertLessThan(3000, $responseTime, 'Dashboard should load in under 3 seconds with large order dataset');

        // Verify data integrity
        $data = $response->original->getData();
        $this->assertEquals($orders->count(), $data['totalOrders']);
        
        // Revenue data should still be properly aggregated
        $this->assertArrayHasKey('revenueData', $data);
        $this->assertCount(30, $data['revenueData']['labels']);
        $this->assertCount(30, $data['revenueData']['data']);
    }
}
