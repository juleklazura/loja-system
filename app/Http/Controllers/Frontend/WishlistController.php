<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\WishlistToggleRequest;
use App\Services\WishlistService;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistService $wishlistService
    ) {}

    /**
     * Display the user's wishlist
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $wishlistItems = $this->wishlistService->getUserWishlist($user);

            if ($request->expectsJson()) {
                $productIds = $wishlistItems->pluck('product.id')->toArray();
                
                return response()->json([
                    'success' => true,
                    'wishlistProductIds' => $productIds,
                    'count' => $wishlistItems->count(),
                    'message' => $wishlistItems->count() > 0 ? 
                        "Você tem {$wishlistItems->count()} item(ns) na lista de desejos" : 
                        'Sua lista de desejos está vazia'
                ]);
            }

            return view('frontend.wishlist.index', compact('wishlistItems'));
            
        } catch (\Exception $e) {
            Log::error('Wishlist index error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro interno do servidor',
                    'wishlistProductIds' => [],
                    'count' => 0
                ], 500);
            }

            return redirect()->back()->withErrors('Erro ao carregar lista de desejos');
        }
    }

    public function toggle(WishlistToggleRequest $request)
    {
        try {
            $productId = $request->validated()['product_id'];
            $user = Auth::user();
            
            $product = Product::where('id', $productId)
                            ->where('active', 1)
                            ->first();
                            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não disponível!'
                ], 404);
            }

            $result = $this->wishlistService->toggleWishlistItem($user, $product);

            Log::info('Wishlist toggled', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'action' => $result['action']
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Erro ao toggle wishlist', [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id ?? 'N/A',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Método simplificado para debug
     */
    public function toggleSimple(Request $request)
    {
        try {
            $productId = $request->input('product_id');
            $user = Auth::user();
            
            if (!$productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product ID é obrigatório'
                ], 400);
            }
            
            $product = Product::where('id', $productId)
                            ->where('active', 1)
                            ->first();
                            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não disponível!'
                ], 404);
            }

            $result = $this->wishlistService->toggleWishlistItem($user, $product);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Legacy add method for backward compatibility
     */
    public function add(WishlistToggleRequest $request)
    {
        return $this->toggle($request);
    }

    /**
     * Legacy remove method for backward compatibility
     */
    public function remove(WishlistToggleRequest $request, Product $product)
    {
        try {
            $user = Auth::user();
            $result = $this->wishlistService->toggleWishlistItem($user, $product);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function clear()
    {
        try {
            $user = Auth::user();
            $this->wishlistService->clearWishlist($user);
            
            return redirect()->route('wishlist.index')
                           ->with('success', 'Lista de desejos limpa com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors('Erro ao limpar lista de desejos');
        }
    }
}
