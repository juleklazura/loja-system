<?php

namespace App\Http\Controllers\Frontend;

use App\Exceptions\Cart\CartItemNotFoundException;
use App\Exceptions\Cart\InvalidQuantityException;
use App\Exceptions\Cart\ProductNotAvailableException;
use App\Exceptions\Product\ProductNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CartAddRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\AuditService;
use App\Services\CartService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected AuditService $auditService
    ) {}

    public function index()
    {
        $startTime = microtime(true);
        
        $user = Auth::user();
        $cartItems = $this->cartService->getCartItems($user);
        $cartTotal = $this->cartService->getCartTotal($user);
        
        // Log de auditoria apenas se configurado
        if (config('audit.log_view_actions', false)) {
            $this->auditService->logCartAction('view', [
                'total_items' => $cartItems->count(),
                'total_value' => $cartTotal,
            ]);
        }
        
        // Log de performance apenas se lenta
        $executionTime = (microtime(true) - $startTime) * 1000;
        $this->auditService->logPerformance('cart.index', $executionTime);
        
        return view('frontend.cart.index', compact('cartItems', 'cartTotal'));
    }

    public function add(CartAddRequest $request)
    {
        try {
            $data = $request->validated();
            $product = Product::findOrFail($data['product_id']);
            
            // Toda validação agora está centralizada no Service
            $result = $this->cartService->addItem(Auth::user(), $product, $data['quantity']);
            
            // Log de auditoria da ação (apenas se configurado)
            if (config('audit.log_cart_actions', true)) {
                $this->auditService->logCartAction('add', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $data['quantity'],
                    'price' => $product->price,
                    'total_value' => $product->price * $data['quantity'],
                ]);
            }
            
            return response()->json($result);
            
        } catch (ModelNotFoundException $e) {
            throw new ProductNotFoundException($request->input('product_id'));
        } catch (ProductNotAvailableException | InvalidQuantityException $e) {
            throw $e; // Re-throw para ser tratado pelo CustomExceptionHandler
        } catch (\Exception $e) {
            $this->auditService->logError($e, 'cart.add', [
                'product_id' => $request->input('product_id'),
                'quantity' => $request->input('quantity'),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno. Tente novamente.'
            ], 500);
        }
    }

    public function update(CartUpdateRequest $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== Auth::id()) {
            abort(403, 'Acesso negado ao item do carrinho');
        }

        try {
            $quantity = $request->validated()['quantity'];
            
            // Toda validação agora está no Service
            $result = $this->cartService->updateQuantity($cartItem, $quantity);
            
            // Log de auditoria
            $this->auditService->logCartAction('update', [
                'cart_item_id' => $cartItem->id,
                'product_id' => $cartItem->product_id,
                'old_quantity' => $cartItem->quantity,
                'new_quantity' => $quantity,
            ]);
            
            return response()->json($result);
        } catch (CartItemNotFoundException | InvalidQuantityException | ProductNotAvailableException $e) {
            throw $e; // Re-throw para ser tratado pelo CustomExceptionHandler
        } catch (\Exception $e) {
            $this->auditService->logError($e, 'cart.update', [
                'cart_item_id' => $cartItem->id,
                'quantity' => $request->input('quantity'),
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Erro interno. Tente novamente.'
            ], 500);
        }
    }

    public function remove(CartItem $cartItem)
    {
        if ($cartItem->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $result = $this->cartService->removeItem($cartItem);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function clear()
    {
        $this->cartService->clearCart(Auth::user());
        return redirect()->route('cart.index')->with('success', 'Carrinho limpo com sucesso');
    }
}
