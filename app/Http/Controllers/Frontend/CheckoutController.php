<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $cartItems = $user->cartItems()->with('product.category')->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Seu carrinho está vazio');
        }
        
        $cartTotal = $cartItems->sum(function ($item) {
            return $item->product->effective_price * $item->quantity;
        });
        
        return view('frontend.checkout.index', compact('cartItems', 'cartTotal'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string',
            'billing_address' => 'nullable|string',
            'payment_method' => 'required|in:credit_card,debit_card,pix,boleto',
            'notes' => 'nullable|string'
        ]);

        $cartItems = auth()->user()->cartItems()->with('product')->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Seu carrinho está vazio!');
        }

        DB::transaction(function () use ($request, $cartItems) {
            $subtotal = $cartItems->sum('total_price');
            $shipping = 10.00;
            $total = $subtotal + $shipping;

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'total_amount' => $total,
                'shipping_amount' => $shipping,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?: $request->shipping_address,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes
            ]);

            // Create order items and update stock
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->product->effective_price,
                    'total_price' => $cartItem->quantity * $cartItem->product->effective_price
                ]);

                // Update product stock
                $cartItem->product->decrement('stock_quantity', $cartItem->quantity);
            }

            // Clear cart
            auth()->user()->cartItems()->delete();
        });

        $order = Order::where('user_id', auth()->id())->latest()->first();
        
        return redirect()->route('checkout.success', $order)->with('success', 'Pedido realizado com sucesso!');
    }

    public function success(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('frontend.checkout.success', compact('order'));
    }

    public function orders()
    {
        $orders = auth()->user()->orders()->with('orderItems.product')->latest()->paginate(10);
        
        return view('frontend.account.orders', compact('orders'));
    }

    public function orderDetail(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('orderItems.product');
        
        return view('frontend.account.order-detail', compact('order'));
    }
}
