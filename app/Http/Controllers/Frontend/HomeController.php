<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Promotion;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Get featured products (latest active products - prioritize in-stock products)
        $featuredProducts = Product::with('category')
            ->active()
            ->orderByRaw('CASE WHEN stock_quantity > 0 THEN 0 ELSE 1 END')
            ->latest()
            ->take(8)
            ->get();

        // Get active categories with products
        $categories = Category::active()
            ->whereHas('products', function($query) {
                $query->where('active', true);
            })
            ->withCount(['products as active_products_count' => function($query) {
                $query->where('active', true);
            }])
            ->take(6)
            ->get();

        // Get current promotions
        $promotions = Promotion::active()
            ->with(['product', 'category'])
            ->take(5)
            ->get();

        return view('frontend.home', compact('featuredProducts', 'categories', 'promotions'));
    }
}
