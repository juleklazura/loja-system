<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Helpers\SecurityHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function getActiveProducts(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::with(['category'])
                        ->where('active', 1)
                        ->where('stock_quantity', '>', 0);

        $query = $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    public function searchProducts(string $searchTerm, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $sanitizedSearch = SecurityHelper::sanitizeSearchQuery($searchTerm);
        
        $query = Product::with(['category'])
                        ->where('active', 1)
                        ->where('stock_quantity', '>', 0)
                        ->where(function($q) use ($sanitizedSearch) {
                            $q->where('name', 'LIKE', "%{$sanitizedSearch}%")
                              ->orWhere('description', 'LIKE', "%{$sanitizedSearch}%")
                              ->orWhere('sku', 'LIKE', "%{$sanitizedSearch}%");
                        });

        $query = $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    public function getProductsByCategory(int $categoryId, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::with(['category'])
                        ->where('active', 1)
                        ->where('stock_quantity', '>', 0)
                        ->where('category_id', $categoryId);

        $query = $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    public function getFeaturedProducts(int $limit = 8)
    {
        return Product::with(['category'])
                     ->where('active', 1)
                     ->where('stock_quantity', '>', 0)
                     ->where('featured', 1)
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    public function getRelatedProducts(Product $product, int $limit = 4)
    {
        return Product::with(['category'])
                     ->where('active', 1)
                     ->where('stock_quantity', '>', 0)
                     ->where('category_id', $product->category_id)
                     ->where('id', '!=', $product->id)
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    public function getProductWithDetails(int $productId): ?Product
    {
        return Product::with(['category', 'reviews.user'])
                     ->where('id', $productId)
                     ->where('active', 1)
                     ->first();
    }

    public function getTopSellingProducts(int $limit = 10)
    {
        return Product::with(['category'])
                     ->where('active', 1)
                     ->where('stock_quantity', '>', 0)
                     ->withCount(['orderItems' => function($query) {
                         $query->whereHas('order', function($q) {
                             $q->whereIn('status', ['delivered', 'shipped']);
                         });
                     }])
                     ->orderBy('order_items_count', 'desc')
                     ->limit($limit)
                     ->get();
    }

    public function getNewArrivals(int $limit = 8)
    {
        return Product::with(['category'])
                     ->where('active', 1)
                     ->where('stock_quantity', '>', 0)
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    public function getProductsOnSale(int $limit = 12)
    {
        return Product::with(['category'])
                     ->where('active', 1)
                     ->where('stock_quantity', '>', 0)
                     ->whereNotNull('sale_price')
                     ->where('sale_price', '>', 0)
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    public function getPriceRange(array $filters = []): array
    {
        $query = Product::where('active', 1)
                        ->where('stock_quantity', '>', 0);

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        $minPrice = $query->min('price');
        $maxPrice = $query->max('price');

        return [
            'min' => $minPrice ?? 0,
            'max' => $maxPrice ?? 0
        ];
    }

    public function updateStock(Product $product, int $quantity, string $operation = 'decrement'): bool
    {
        if ($operation === 'decrement') {
            if ($product->stock_quantity < $quantity) {
                throw new \Exception('Estoque insuficiente');
            }
            $product->decrement('stock_quantity', $quantity);
        } else {
            $product->increment('stock_quantity', $quantity);
        }

        return true;
    }

    public function checkAvailability(Product $product, int $requestedQuantity = 1): array
    {
        $isAvailable = $product->active && 
                      $product->stock_quantity >= $requestedQuantity;

        return [
            'available' => $isAvailable,
            'stock_quantity' => $product->stock_quantity,
            'requested_quantity' => $requestedQuantity,
            'message' => $isAvailable ? 
                'Produto disponível' : 
                'Produto indisponível ou quantidade insuficiente'
        ];
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        // Price filter
        if (isset($filters['min_price']) && $filters['min_price'] > 0) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price']) && $filters['max_price'] > 0) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Category filter
        if (isset($filters['category_id']) && $filters['category_id'] > 0) {
            $query->where('category_id', $filters['category_id']);
        }

        // On sale filter
        if (isset($filters['on_sale']) && $filters['on_sale']) {
            $query->whereNotNull('sale_price')->where('sale_price', '>', 0);
        }

        // Featured filter
        if (isset($filters['featured']) && $filters['featured']) {
            $query->where('featured', 1);
        }

        // Sort options
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        switch ($sortBy) {
            case 'price':
                $query->orderBy('price', $sortDirection);
                break;
            case 'name':
                $query->orderBy('name', $sortDirection);
                break;
            case 'popularity':
                $query->withCount('orderItems')
                      ->orderBy('order_items_count', $sortDirection);
                break;
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }

        return $query;
    }
}
