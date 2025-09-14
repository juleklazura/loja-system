<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class OrderService
{
    public function createOrderFromCart(User $user, array $shippingData, ?string $couponCode = null): Order
    {
        return DB::transaction(function() use ($user, $shippingData, $couponCode) {
            // Get cart items
            $cartItems = CartItem::with('product')
                                ->where('user_id', $user->id)
                                ->get();

            if ($cartItems->isEmpty()) {
                throw new \Exception('Carrinho vazio');
            }

            // Validate stock
            foreach ($cartItems as $cartItem) {
                if ($cartItem->quantity > $cartItem->product->stock_quantity) {
                    throw new \Exception("Produto '{$cartItem->product->name}' não tem estoque suficiente");
                }
            }

            // Calculate totals
            $subtotal = $cartItems->sum(function($item) {
                return $item->product->effective_price * $item->quantity;
            });

            $shippingCost = $this->calculateShippingCost($shippingData, $cartItems);
            $discount = 0;

            // Apply coupon if provided
            if ($couponCode) {
                $discount = $this->applyCoupon($couponCode, $subtotal);
            }

            $total = $subtotal + $shippingCost - $discount;

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'status' => 'pending',
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'total' => $total,
                'shipping_address' => json_encode($shippingData),
                'coupon_code' => $couponCode
            ]);

            // Create order items and update stock
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->effective_price,
                    'product_name' => $cartItem->product->name,
                    'product_data' => json_encode([
                        'sku' => $cartItem->product->sku,
                        'image' => $cartItem->product->image_url
                    ])
                ]);

                // Update product stock
                $cartItem->product->decrement('stock_quantity', $cartItem->quantity);
            }

            // Clear cart
            CartItem::where('user_id', $user->id)->delete();

            return $order->load('items');
        });
    }

    public function getUserOrders(User $user, int $perPage = 10)
    {
        return Order::with(['items.product'])
                   ->where('user_id', $user->id)
                   ->orderBy('created_at', 'desc')
                   ->paginate($perPage);
    }

    public function getOrderByNumber(string $orderNumber, ?User $user = null): ?Order
    {
        $query = Order::with(['items.product'])
                     ->where('order_number', $orderNumber);

        if ($user) {
            $query->where('user_id', $user->id);
        }

        return $query->first();
    }

    public function updateOrderStatus(Order $order, string $status): bool
    {
        $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \Exception('Status inválido');
        }

        return $order->update(['status' => $status]);
    }

    public function updatePaymentStatus(Order $order, string $paymentStatus): bool
    {
        $validStatuses = ['pending', 'paid', 'failed', 'refunded'];
        
        if (!in_array($paymentStatus, $validStatuses)) {
            throw new \Exception('Status de pagamento inválido');
        }

        return $order->update(['payment_status' => $paymentStatus]);
    }

    public function cancelOrder(Order $order, string $reason = null): bool
    {
        if ($order->status === 'cancelled') {
            throw new \Exception('Pedido já cancelado');
        }

        if (in_array($order->status, ['shipped', 'delivered'])) {
            throw new \Exception('Não é possível cancelar pedido já enviado ou entregue');
        }

        return DB::transaction(function() use ($order, $reason) {
            // Restore stock
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }

            // Update order
            $order->update([
                'status' => 'cancelled',
                'cancelled_reason' => $reason,
                'cancelled_at' => now()
            ]);

            return true;
        });
    }

    public function getOrderStats(User $user): array
    {
        $orders = Order::where('user_id', $user->id)->get();

        return [
            'total_orders' => $orders->count(),
            'total_spent' => $orders->sum('total'),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'completed_orders' => $orders->where('status', 'delivered')->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count()
        ];
    }

    private function generateOrderNumber(): string
    {
        return 'ORD' . date('Ymd') . str_pad(
            Order::whereDate('created_at', today())->count() + 1, 
            4, 
            '0', 
            STR_PAD_LEFT
        );
    }

    private function calculateShippingCost(array $shippingData, Collection $cartItems): float
    {
        // Simple shipping calculation - can be enhanced with real API
        $totalWeight = $cartItems->sum(function($item) {
            return $item->product->weight * $item->quantity;
        });

        // Base shipping cost
        $baseCost = 15.00;
        
        // Additional cost per kg
        $weightCost = max(0, ($totalWeight - 1)) * 2.50;

        return $baseCost + $weightCost;
    }

    private function applyCoupon(string $couponCode, float $subtotal): float
    {
        // Simple coupon logic - can be enhanced with Coupon model
        $validCoupons = [
            'DESCONTO10' => 0.10,
            'PRIMEIRA-COMPRA' => 0.15,
            'FRETE-GRATIS' => 0.05
        ];

        if (!isset($validCoupons[$couponCode])) {
            throw new \Exception('Cupom inválido');
        }

        return $subtotal * $validCoupons[$couponCode];
    }
}
