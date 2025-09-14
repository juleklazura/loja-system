<?php

namespace Database\Factories;

use App\Models\CartItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'created_at' => fake()->dateTimeBetween('-1 month'),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the cart item is for a specific user.
     */
    public function forUser(User|int $user): static
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    /**
     * Indicate that the cart item is for a specific product.
     */
    public function forProduct(Product|int $product): static
    {
        $productId = $product instanceof Product ? $product->id : $product;
        
        return $this->state(fn (array $attributes) => [
            'product_id' => $productId,
        ]);
    }

    /**
     * Set specific quantity for cart item.
     */
    public function withQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
        ]);
    }

    /**
     * Set specific price for cart item.
     */
    public function withPrice(float $price): static
    {
        // Note: CartItem doesn't store price directly, price comes from Product
        return $this;
    }

    /**
     * Create multiple cart items for same user.
     */
    public function forSameUser(): static
    {
        $user = User::factory()->create();
        
        return $this->forUser($user);
    }
}
