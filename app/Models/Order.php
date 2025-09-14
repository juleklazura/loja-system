<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'total_amount',
        'discount_amount',
        'shipping_amount',
        'shipping_address',
        'billing_address',
        'payment_method',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
    ];

    /**
     * Get the user for this order
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get order items for this order
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Alias for orderItems (for backward compatibility)
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Generate unique order number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $order->order_number = 'ORD-' . date('Y') . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Calculate subtotal
     */
    public function getSubtotalAttribute()
    {
        return $this->orderItems->sum('total_price');
    }

    /**
     * Calculate final amount
     */
    public function getFinalAmountAttribute()
    {
        return $this->subtotal - $this->discount_amount + $this->shipping_amount;
    }

    /**
     * Scope for dashboard recent orders with optimized joins
     */
    public function scopeForDashboard($query)
    {
        return $query->select(['orders.id', 'orders.total_amount as total', 'orders.status', 'orders.created_at', 'users.name as user_name'])
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->latest('orders.created_at');
    }

    /**
     * Scope for revenue calculations optimized
     */
    public function scopeRevenueBetweenDates($query, $startDate, $endDate)
    {
        return $query->selectRaw('DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date');
    }

    /**
     * Scope for dashboard statistics
     */
    public function scopeDashboardStats($query)
    {
        return $query->selectRaw('COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_revenue');
    }
}
