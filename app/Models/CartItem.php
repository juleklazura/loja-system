<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    // Otimização: Adicionar índices para consultas frequentes
    protected static function boot()
    {
        parent::boot();
        
        // Limpar cache quando item for criado/atualizado/deletado
        static::saved(function ($cartItem) {
            self::clearUserCartCache($cartItem->user_id);
        });
        
        static::deleted(function ($cartItem) {
            self::clearUserCartCache($cartItem->user_id);
        });
    }

    /**
     * Get the user for this cart item
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product for this cart item - otimizado com select específico
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->select([
            'id', 'name', 'price', 'promotional_price', 'category_id', 'sku', 'images', 'stock_quantity'
        ]);
    }

    /**
     * Get total price for this item
     */
    public function getTotalPriceAttribute()
    {
        return $this->quantity * $this->product->effective_price;
    }

    /**
     * Otimização: Método estático para obter quantidade total do carrinho com cache
     */
    public static function getUserCartQuantity($userId)
    {
        return Cache::remember(
            "cart_quantity_{$userId}",
            300, // 5 minutos
            fn() => self::where('user_id', $userId)->sum('quantity')
        );
    }

    /**
     * Otimização: Método estático para obter contagem de itens do carrinho com cache
     */
    public static function getUserCartCount($userId)
    {
        return Cache::remember(
            "cart_count_{$userId}",
            300, // 5 minutos
            fn() => self::where('user_id', $userId)->count()
        );
    }

    /**
     * Limpar cache do carrinho do usuário
     */
    public static function clearUserCartCache($userId)
    {
        Cache::forget("cart_quantity_{$userId}");
        Cache::forget("cart_count_{$userId}");
        Cache::forget("cart_items_{$userId}");
    }

    /**
     * Otimização: Scope para carregar carrinho com produtos relacionados eficientemente
     */
    public function scopeWithProductsOptimized($query, $userId)
    {
        return $query->where('user_id', $userId)
                    ->with(['product' => function($query) {
                        $query->select(['id', 'name', 'price', 'promotional_price', 'category_id', 'sku', 'images', 'stock_quantity'])
                              ->with(['category:id,name']);
                    }]);
    }
}
