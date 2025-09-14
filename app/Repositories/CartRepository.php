<?php

namespace App\Repositories;

use App\Contracts\CartRepositoryInterface;
use App\Models\CartItem;
use App\Services\CacheService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CartRepository implements CartRepositoryInterface
{
    public function __construct(
        private CacheService $cacheService
    ) {}
    
    public function getCartItemsByUser(int $userId): Collection
    {
        return $this->cacheService->cacheUserCart($userId, function() use ($userId) {
            return CartItem::where('user_id', $userId)
                ->with([
                    'product:id,name,price,promotional_price,stock_quantity,active,category_id',
                    'product.category:id,name'
                ])
                ->select(['id', 'user_id', 'product_id', 'quantity', 'created_at', 'updated_at'])
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }
    
    public function findCartItem(int $userId, int $productId): ?CartItem
    {
        $key = "cart:item:user:{$userId}:product:{$productId}";
        
        return $this->cacheService->remember($key, 300, function() use ($userId, $productId) {
            return CartItem::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();
        }, ['cart', "user:{$userId}"]);
    }
    
    public function createCartItem(array $data): CartItem
    {
        $cartItem = CartItem::create($data);
        $this->cacheService->invalidateCartCache($data['user_id']);
        return $cartItem;
    }
    
    public function updateCartItem(CartItem $cartItem, array $data): bool
    {
        $result = $cartItem->update($data);
        $this->cacheService->invalidateCartCache($cartItem->user_id);
        return $result;
    }
    
    public function deleteCartItem(CartItem $cartItem): bool
    {
        $userId = $cartItem->user_id;
        $result = $cartItem->delete();
        $this->cacheService->invalidateCartCache($userId);
        return $result;
    }
    
    public function clearUserCart(int $userId): bool
    {
        $result = CartItem::where('user_id', $userId)->delete();
        $this->cacheService->invalidateCartCache($userId);
        return $result > 0;
    }
    
    public function getCartCount(int $userId): int
    {
        return $this->cacheService->cacheCartCount($userId, function() use ($userId) {
            return CartItem::where('user_id', $userId)->sum('quantity');
        });
    }
    
    public function getCartTotal(int $userId): float
    {
        return $this->cacheService->cacheCartTotal($userId, function() use ($userId) {
            return CartItem::where('user_id', $userId)
                ->join('products', 'cart_items.product_id', '=', 'products.id')
                ->sum(DB::raw('cart_items.quantity * products.price'));
        });
    }
}
