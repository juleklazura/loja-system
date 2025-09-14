<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'product_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Otimização: Gerenciar cache automaticamente
    protected static function boot()
    {
        parent::boot();
        
        // Limpar cache quando item for criado/atualizado/deletado
        static::saved(function ($wishlistItem) {
            self::clearUserWishlistCache($wishlistItem->user_id);
        });
        
        static::deleted(function ($wishlistItem) {
            self::clearUserWishlistCache($wishlistItem->user_id);
        });
    }

    /**
     * Get the user that owns this wishlist item
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product in this wishlist item - otimizado com select específico
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->select([
            'id', 'name', 'price', 'promotional_price', 'category_id', 'sku', 'images',
            'stock_quantity', 'active', 'description'
        ]);
    }

    /**
     * Otimização: Método estático para obter contagem da wishlist com cache
     */
    public static function getUserWishlistCount($userId)
    {
        return Cache::remember(
            "wishlist_count_{$userId}",
            300, // 5 minutos
            fn() => self::where('user_id', $userId)->count()
        );
    }

    /**
     * Otimização: Verificar se produto está na wishlist com cache
     */
    public static function isProductInUserWishlist($userId, $productId)
    {
        $wishlistItems = Cache::remember(
            "wishlist_items_{$userId}",
            300,
            fn() => self::where('user_id', $userId)->pluck('product_id')->toArray()
        );
        
        return in_array($productId, $wishlistItems);
    }

    /**
     * Limpar cache da wishlist do usuário
     */
    public static function clearUserWishlistCache($userId)
    {
        Cache::forget("wishlist_count_{$userId}");
        Cache::forget("wishlist_items_{$userId}");
    }

    /**
     * Otimização: Scope para carregar wishlist com produtos relacionados eficientemente
     */
    public function scopeWithProductsOptimized($query, $userId)
    {
        return $query->where('user_id', $userId)
                    ->with(['product' => function($query) {
                        $query->select(['id', 'name', 'price', 'promotional_price', 'category_id', 'sku', 'images', 'stock_quantity', 'active', 'description'])
                              ->with(['category:id,name']);
                    }]);
    }
}
