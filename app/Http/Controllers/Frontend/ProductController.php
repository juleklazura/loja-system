<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Helpers\SecurityHelper;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')
            ->active();

        // Filter by stock availability - only apply if checkbox is checked
        if ($request->filled('in_stock') && $request->in_stock == '1') {
            $query->inStock();
        }

        // Filter by category
        if ($request->filled('category_id') && SecurityHelper::validateProductId($request->category_id)) {
            $query->where('category_id', (int)$request->category_id);
        }

        // Search by name
        if ($request->filled('search')) {
            $searchTerm = SecurityHelper::sanitizeSearchQuery($request->search);
            if (!empty($searchTerm)) {
                $query->where('name', 'like', '%' . $searchTerm . '%');
            }
        }

        // Filter by price range
        if ($request->filled('min_price') && SecurityHelper::validatePrice($request->min_price)) {
            $query->where('price', '>=', (float)$request->min_price);
        }
        if ($request->filled('max_price') && SecurityHelper::validatePrice($request->max_price)) {
            $query->where('price', '<=', (float)$request->max_price);
        }

        // Filter by promotions
        if ($request->filled('on_promotion')) {
            $query->whereHas('promotions', function($q) {
                $q->where('active', true)
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
            });
        }

        // Sort products - always prioritize in-stock products first
        $sortBy = $request->get('sort', 'latest');
        
        // First order by stock status (in stock products first)
        $query->orderByRaw('CASE WHEN stock_quantity > 0 THEN 0 ELSE 1 END');
        
        // Then apply the requested sorting
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(12);
        $categories = Category::active()->withCount('activeProducts')->get();

        return view('frontend.products.index', compact('products', 'categories', 'sortBy'));
    }

    public function show(Product $product)
    {
        $product->load('category', 'promotions');
        
        // Get related products from same category - prioritize in-stock products
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('active', true)
            ->orderByRaw('CASE WHEN stock_quantity > 0 THEN 0 ELSE 1 END')
            ->latest()
            ->limit(8)
            ->get();
        
        return view('frontend.products.show', compact('product', 'relatedProducts'));
    }
    
    public function category(Category $category, Request $request)
    {
        if (!$category->active) {
            abort(404);
        }

        $query = Product::where('category_id', $category->id)
            ->where('active', true)
            ->with('category', 'promotions');

        // Get sort option
        $sortBy = $request->get('sort', 'latest');
        
        // First order by stock status (in stock products first)
        $query->orderByRaw('CASE WHEN stock_quantity > 0 THEN 0 ELSE 1 END');
        
        // Then apply the requested sorting
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'price_asc':
                $query->orderBy('price');
                break;
            case 'price_desc':
                $query->orderByDesc('price');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(12);
            
        return view('frontend.products.index', [
            'products' => $products,
            'categories' => Category::active()->get(),
            'selectedCategory' => $category,
            'sortBy' => $sortBy,
            'filters' => [
                'search' => '',
                'category' => $category->id,
                'min_price' => '',
                'max_price' => '',
                'in_stock' => '',
                'on_promotion' => ''
            ]
        ]);
    }
}
