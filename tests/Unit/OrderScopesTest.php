<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class OrderScopesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function scope_for_dashboard_returns_optimized_fields()
    {
        // Arrange
        $user = User::factory()->create(['name' => 'John Doe']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 150.75,
            'status' => 'pending'
        ]);

        // Act
        $result = Order::forDashboard()->first();

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($order->id, $result->id);
        $this->assertEquals(150.75, $result->total);
        $this->assertEquals('pending', $result->status);
        $this->assertEquals('John Doe', $result->user_name);
        
        // Verify it has the expected fields from the join
        $this->assertTrue(isset($result->user_name));
        $this->assertTrue(isset($result->total));
        $this->assertTrue(isset($result->created_at));
    }

    /** @test */
    public function scope_for_dashboard_orders_by_latest()
    {
        // Arrange
        $user = User::factory()->create();
        
        $oldOrder = Order::factory()->create([
            'user_id' => $user->id,
            'created_at' => Carbon::now()->subDays(2)
        ]);
        
        $newOrder = Order::factory()->create([
            'user_id' => $user->id,
            'created_at' => Carbon::now()->subDay()
        ]);

        // Act
        $results = Order::forDashboard()->get();

        // Assert
        $this->assertCount(2, $results);
        $this->assertEquals($newOrder->id, $results->first()->id); // Most recent first
        $this->assertEquals($oldOrder->id, $results->last()->id);
    }

    /** @test */
    public function scope_revenue_between_dates_calculates_correctly()
    {
        // Arrange
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();
        
        // Orders within date range
        Order::factory()->create([
            'created_at' => Carbon::now()->subDays(5),
            'total_amount' => 100.00
        ]);
        
        Order::factory()->create([
            'created_at' => Carbon::now()->subDays(3),
            'total_amount' => 200.00
        ]);
        
        // Another order on same day
        Order::factory()->create([
            'created_at' => Carbon::now()->subDays(3),
            'total_amount' => 50.00
        ]);
        
        // Order outside date range
        Order::factory()->create([
            'created_at' => Carbon::now()->subDays(10),
            'total_amount' => 500.00
        ]);

        // Act
        $results = Order::revenueBetweenDates($startDate, $endDate)->get();

        // Assert
        $this->assertCount(2, $results); // 2 different dates within range
        
        // Check totals by date
        $dateResults = $results->keyBy('date');
        $this->assertEquals(100.00, $dateResults[Carbon::now()->subDays(5)->toDateString()]->total);
        $this->assertEquals(250.00, $dateResults[Carbon::now()->subDays(3)->toDateString()]->total); // 200 + 50
    }

    /** @test */
    public function scope_revenue_between_dates_groups_by_date()
    {
        // Arrange
        $date = Carbon::now()->subDays(2)->toDateString();
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();
        
        // Multiple orders on same date
        Order::factory()->create([
            'created_at' => $date . ' 10:00:00',
            'total_amount' => 75.50
        ]);
        
        Order::factory()->create([
            'created_at' => $date . ' 15:30:00',
            'total_amount' => 124.50
        ]);

        // Act
        $results = Order::revenueBetweenDates($startDate, $endDate)->get();

        // Assert
        $this->assertCount(1, $results); // Only one date group
        $this->assertEquals($date, $results->first()->date);
        $this->assertEquals(200.00, $results->first()->total); // Sum of both orders
    }

    /** @test */
    public function scope_revenue_between_dates_orders_by_date()
    {
        // Arrange
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();
        
        Order::factory()->create([
            'created_at' => Carbon::now()->subDays(3),
            'total_amount' => 100.00
        ]);
        
        Order::factory()->create([
            'created_at' => Carbon::now()->subDays(1),
            'total_amount' => 200.00
        ]);
        
        Order::factory()->create([
            'created_at' => Carbon::now()->subDays(5),
            'total_amount' => 150.00
        ]);

        // Act
        $results = Order::revenueBetweenDates($startDate, $endDate)->get();

        // Assert
        $this->assertCount(3, $results);
        
        // Verify ordering (oldest first due to ORDER BY date)
        $dates = $results->pluck('date')->toArray();
        $this->assertEquals(Carbon::now()->subDays(5)->toDateString(), $dates[0]);
        $this->assertEquals(Carbon::now()->subDays(3)->toDateString(), $dates[1]);
        $this->assertEquals(Carbon::now()->subDays(1)->toDateString(), $dates[2]);
    }

    /** @test */
    public function scope_dashboard_stats_calculates_totals()
    {
        // Arrange
        Order::factory()->count(5)->create(['total_amount' => 100.00]);
        Order::factory()->count(3)->create(['total_amount' => 200.00]);

        // Act
        $stats = Order::dashboardStats()->first();

        // Assert
        $this->assertEquals(8, $stats->total_orders);
        $this->assertEquals(1100.00, $stats->total_revenue); // (5 * 100) + (3 * 200)
    }

    /** @test */
    public function scope_dashboard_stats_handles_empty_data()
    {
        // Act - No orders in database
        $stats = Order::dashboardStats()->first();

        // Assert
        $this->assertEquals(0, $stats->total_orders);
        $this->assertEquals(0, $stats->total_revenue);
    }

    /** @test */
    public function scope_revenue_between_dates_handles_empty_range()
    {
        // Arrange
        Order::factory()->create(['total_amount' => 100.00]);
        
        $startDate = Carbon::now()->addDays(1); // Future date
        $endDate = Carbon::now()->addDays(2);

        // Act
        $results = Order::revenueBetweenDates($startDate, $endDate)->get();

        // Assert
        $this->assertCount(0, $results);
    }

    /** @test */
    public function scope_for_dashboard_includes_correct_join()
    {
        // Arrange
        $user1 = User::factory()->create(['name' => 'Alice']);
        $user2 = User::factory()->create(['name' => 'Bob']);
        
        Order::factory()->create(['user_id' => $user1->id]);
        Order::factory()->create(['user_id' => $user2->id]);

        // Act
        $results = Order::forDashboard()->get();

        // Assert
        $this->assertCount(2, $results);
        
        $userNames = $results->pluck('user_name')->toArray();
        $this->assertContains('Alice', $userNames);
        $this->assertContains('Bob', $userNames);
    }

    /** @test */
    public function scopes_can_be_combined_with_other_query_methods()
    {
        // Arrange
        $user = User::factory()->create();
        Order::factory()->count(10)->create(['user_id' => $user->id]);

        // Act
        $limitedResults = Order::forDashboard()->limit(5)->get();
        $countResult = Order::forDashboard()->count();

        // Assert
        $this->assertCount(5, $limitedResults);
        $this->assertEquals(10, $countResult);
    }
}
