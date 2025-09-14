<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;

class WishlistService
{
    public function toggleWishlistItem(User $user, Product $product): array
    {
        try {
            $wishlistItem = Wishlist::where('user_id', $user->id)
                                  ->where('product_id', $product->id)
                                  ->first();

            return DB::transaction(function() use ($wishlistItem, $user, $product) {
                if ($wishlistItem) {
                    $wishlistItem->delete();
                    return [
                        'success' => true,
                        'action' => 'removed',
                        'message' => 'Produto removido da lista de desejos',
                        'is_in_wishlist' => false,
                        'wishlist_count' => $this->getUserWishlistCount($user)
                    ];
                } else {
                    Wishlist::create([
                        'user_id' => $user->id,
                        'product_id' => $product->id
                    ]);
                    
                    return [
                        'success' => true,
                        'action' => 'added',
                        'message' => 'Produto adicionado à lista de desejos',
                        'is_in_wishlist' => true,
                        'wishlist_count' => $this->getUserWishlistCount($user)
                    ];
                }
            });
        } catch (\Exception $e) {
            throw new \Exception('Erro ao atualizar lista de desejos: ' . $e->getMessage());
        }
    }

    public function getUserWishlist(User $user)
    {
        return Wishlist::with(['product.category'])
                      ->where('user_id', $user->id)
                      ->get();
    }

    public function getUserWishlistCount(User $user): int
    {
        return Wishlist::where('user_id', $user->id)->count();
    }

    public function isInWishlist(User $user, Product $product): bool
    {
        return Wishlist::where('user_id', $user->id)
                      ->where('product_id', $product->id)
                      ->exists();
    }

    public function getWishlistProductIds(User $user): array
    {
        return Wishlist::where('user_id', $user->id)
                      ->pluck('product_id')
                      ->toArray();
    }

    public function clearWishlist(User $user): bool
    {
        try {
            return DB::transaction(function() use ($user) {
                Wishlist::where('user_id', $user->id)->delete();
                return true;
            });
        } catch (\Exception $e) {
            throw new \Exception('Erro ao limpar lista de desejos: ' . $e->getMessage());
        }
    }

    public function removeMultipleItems(User $user, array $productIds): array
    {
        try {
            return DB::transaction(function() use ($user, $productIds) {
                $deletedCount = Wishlist::where('user_id', $user->id)
                                      ->whereIn('product_id', $productIds)
                                      ->delete();
                
                return [
                    'success' => true,
                    'message' => "{$deletedCount} itens removidos da lista de desejos",
                    'removed_count' => $deletedCount,
                    'wishlist_count' => $this->getUserWishlistCount($user)
                ];
            });
        } catch (\Exception $e) {
            throw new \Exception('Erro ao remover itens da lista de desejos: ' . $e->getMessage());
        }
    }
}
