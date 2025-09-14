<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get products for this category
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get active products for this category
     */
    public function activeProducts()
    {
        return $this->hasMany(Product::class)->where('active', true);
    }

    /**
     * Get promotions for this category
     */
    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    /**
     * Scope to get only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope for dashboard categories with product counts using optimized query
     */
    public function scopeWithProductCountsOptimized($query)
    {
        return $query->select(['categories.id', 'categories.name'])
            ->withCount(['products' => function($query) {
                $query->select(DB::raw('COUNT(*)'));
            }]);
    }
}
