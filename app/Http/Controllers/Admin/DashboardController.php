<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardCacheService;

    public function __construct(DashboardCacheService $dashboardCacheService)
    {
        $this->dashboardCacheService = $dashboardCacheService;
    }

    public function index()
    {
        // Get all dashboard data using optimized cache service
        $dashboardData = $this->dashboardCacheService->getDashboardData();
        
        // Safely extract data for view
        $totalProducts = $dashboardData['totalProducts'];
        $totalCategories = $dashboardData['totalCategories'];
        $totalOrders = $dashboardData['totalOrders'];
        $totalUsers = $dashboardData['totalUsers'];
        $recentOrders = $dashboardData['recentOrders'];
        $lowStockProducts = $dashboardData['lowStockProducts'];
        $categoriesWithCounts = $dashboardData['categoriesWithCounts'];
        $revenueData = $dashboardData['revenueData'];

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalCategories', 
            'totalOrders',
            'totalUsers',
            'recentOrders',
            'lowStockProducts',
            'categoriesWithCounts',
            'revenueData'
        ));
    }

    /**
     * Clear dashboard cache (for development/testing)
     */
    public function clearCache()
    {
        $this->dashboardCacheService->clearDashboardCache();
        
        return redirect()->route('admin.dashboard')
            ->with('success', 'Cache do dashboard limpo com sucesso!');
    }
}
