<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardCacheService
{
    const CACHE_PREFIX = 'dashboard_';
    const CACHE_TTL = 300; // 5 minutes in seconds

    /**
     * Get cached statistics or fetch from database
     */
    public function getCachedStatistics(): array
    {
        return Cache::remember(self::CACHE_PREFIX . 'statistics', self::CACHE_TTL, function() {
            $stats = DB::select("
                SELECT 'products' as type, COUNT(*) as total FROM products
                UNION ALL
                SELECT 'categories' as type, COUNT(*) as total FROM categories  
                UNION ALL
                SELECT 'orders' as type, COUNT(*) as total FROM orders
                UNION ALL
                SELECT 'users' as type, COUNT(*) as total FROM users
            ");
            
            return collect($stats)->pluck('total', 'type')->toArray();
        });
    }

    /**
     * Get cached recent orders or fetch from database
     */
    public function getCachedRecentOrders(int $limit = 5)
    {
        return Cache::remember(self::CACHE_PREFIX . 'recent_orders', self::CACHE_TTL, function() use ($limit) {
            return Order::forDashboard()
                ->limit($limit)
                ->get()
                ->map(function($order) {
                    $order->user = (object)['name' => $order->user_name];
                    return $order;
                });
        });
    }

    /**
     * Get cached low stock products or fetch from database
     */
    public function getCachedLowStockProducts(int $threshold = 20, int $limit = 10)
    {
        return Cache::remember(self::CACHE_PREFIX . 'low_stock_products', self::CACHE_TTL, function() use ($threshold, $limit) {
            return Product::lowStockForDashboard($threshold)
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get cached categories with counts or fetch from database
     */
    public function getCachedCategoriesWithCounts()
    {
        return Cache::remember(self::CACHE_PREFIX . 'categories_with_counts', self::CACHE_TTL, function() {
            return Category::withProductCountsOptimized()->get();
        });
    }

    /**
     * Get cached revenue data or fetch from database
     */
    public function getCachedRevenueData(int $days = 30): array
    {
        return Cache::remember(self::CACHE_PREFIX . 'revenue_data_' . $days, self::CACHE_TTL, function() use ($days) {
            $startDate = Carbon::now()->subDays($days - 1);
            $endDate = Carbon::now();
            
            $revenueDataRaw = Order::revenueBetweenDates($startDate, $endDate)
                ->get()
                ->keyBy('date');
                
            $revenueData = [
                'labels' => [],
                'data' => []
            ];
            
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dateString = $date->toDateString();
                $revenueData['labels'][] = $date->format('d/m');
                $revenueData['data'][] = $revenueDataRaw->get($dateString)->total ?? 0;
            }
            
            return $revenueData;
        });
    }

    /**
     * Clear all dashboard cache
     */
    public function clearDashboardCache(): void
    {
        $cacheKeys = [
            self::CACHE_PREFIX . 'statistics',
            self::CACHE_PREFIX . 'recent_orders',
            self::CACHE_PREFIX . 'low_stock_products', 
            self::CACHE_PREFIX . 'categories_with_counts',
            self::CACHE_PREFIX . 'revenue_data_30'
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get dashboard data with cache
     */
    public function getDashboardData(): array
    {
        $stats = $this->getCachedStatistics();
        
        return [
            'totalProducts' => $stats['products'] ?? 0,
            'totalCategories' => $stats['categories'] ?? 0,
            'totalOrders' => $stats['orders'] ?? 0,
            'totalUsers' => $stats['users'] ?? 0,
            'recentOrders' => $this->getCachedRecentOrders(),
            'lowStockProducts' => $this->getCachedLowStockProducts(),
            'categoriesWithCounts' => $this->getCachedCategoriesWithCounts(),
            'revenueData' => $this->getCachedRevenueData()
        ];
    }
}
