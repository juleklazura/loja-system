<?php

namespace App\Contracts;

use App\Models\CartItem;
use App\Models\User;
use Illuminate\Support\Collection;

interface CartRepositoryInterface
{
    public function getCartItemsByUser(int $userId): Collection;
    public function findCartItem(int $userId, int $productId): ?CartItem;
    public function createCartItem(array $data): CartItem;
    public function updateCartItem(CartItem $cartItem, array $data): bool;
    public function deleteCartItem(CartItem $cartItem): bool;
    public function clearUserCart(int $userId): bool;
    public function getCartCount(int $userId): int;
    public function getCartTotal(int $userId): float;
}
