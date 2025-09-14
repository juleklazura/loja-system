<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'value',
        'start_date',
        'end_date',
        'active',
        'product_id',
        'category_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'value' => 'decimal:2',
    ];

    /**
     * Get the product for this promotion
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the category for this promotion
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Check if promotion is currently active
     */
    public function isCurrentlyActive()
    {
        return $this->active && 
               $this->start_date <= now()->toDateString() && 
               $this->end_date >= now()->toDateString();
    }

    /**
     * Scope to get only active promotions
     */
    public function scopeActive($query)
    {
        return $query->where('active', true)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }
}
