<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 3),
            'price' => fake()->randomFloat(2, 15, 300),
            'created_at' => fake()->dateTimeBetween('-6 months'),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the order item belongs to a specific order.
     */
    public function forOrder(Order|int $order): static
    {
        $orderId = $order instanceof Order ? $order->id : $order;
        
        return $this->state(fn (array $attributes) => [
            'order_id' => $orderId,
        ]);
    }

    /**
     * Indicate that the order item is for a specific product.
     */
    public function forProduct(Product|int $product): static
    {
        $productId = $product instanceof Product ? $product->id : $product;
        
        return $this->state(fn (array $attributes) => [
            'product_id' => $productId,
        ]);
    }

    /**
     * Set specific quantity for order item.
     */
    public function withQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
        ]);
    }

    /**
     * Set specific price for order item.
     */
    public function withPrice(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $price,
        ]);
    }

    /**
     * Create expensive order items.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => fake()->randomFloat(2, 200, 1000),
            'quantity' => fake()->numberBetween(1, 2),
        ]);
    }

    /**
     * Create bulk order items.
     */
    public function bulk(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(5, 20),
            'price' => fake()->randomFloat(2, 5, 50),
        ]);
    }
}
