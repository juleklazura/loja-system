<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'promotional_price',
        'stock_quantity',
        'min_stock',
        'sku',
        'images',
        'active',
        'category_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'images' => 'array',
        'price' => 'decimal:2',
        'promotional_price' => 'decimal:2',
    ];

    /**
     * Get the category for this product
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get order items for this product
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get cart items for this product
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get promotions for this product
     */
    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    /**
     * Get the effective price (promotional if available and active)
     */
    public function getEffectivePriceAttribute()
    {
        $activePromotion = $this->promotions()
            ->where('active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if ($activePromotion) {
            if ($activePromotion->type === 'percentage') {
                return $this->price * (1 - $activePromotion->value / 100);
            } else {
                return max(0, $this->price - $activePromotion->value);
            }
        }

        return $this->promotional_price ?? $this->price;
    }

    /**
     * Check if product is in stock
     */
    public function isInStock()
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Check if stock is low
     */
    public function isLowStock()
    {
        return $this->stock_quantity <= $this->min_stock;
    }

    /**
     * Scope to get only active products
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get only products in stock
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope for dashboard low stock products with optimized fields
     */
    public function scopeLowStockForDashboard($query, $threshold = 20)
    {
        return $query->select(['id', 'name', 'sku', 'stock_quantity'])
            ->where('stock_quantity', '<', $threshold)
            ->orderBy('stock_quantity', 'asc');
    }

    /**
     * Scope for dashboard with optimized fields and joins
     */
    public function scopeForDashboard($query)
    {
        return $query->select([
                'products.id',
                'products.name',
                'products.price',
                'products.stock_quantity',
                'categories.name as category_name'
            ])
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->orderBy('products.created_at', 'desc');
    }

    /**
     * Scope to get featured products (using promotional_price as indicator)
     */
    public function scopeFeatured($query)
    {
        return $query->whereNotNull('promotional_price');
    }

    /**
     * Scope to get low stock products
     */
    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->where('stock_quantity', '<=', $threshold)
            ->orderBy('stock_quantity', 'asc');
    }

    /**
     * Scope for dashboard statistics
     */
    public function scopeDashboardStats($query)
    {
        return $query->selectRaw('
            COUNT(*) as total_products,
            SUM(stock_quantity) as total_stock,
            COUNT(CASE WHEN promotional_price IS NOT NULL THEN 1 END) as featured_count,
            COUNT(CASE WHEN stock_quantity <= 10 THEN 1 END) as low_stock_count
        ');
    }
}
