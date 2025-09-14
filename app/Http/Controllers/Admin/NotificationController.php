<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        $notifications = [];
        
        // Notificações de vendas (últimas 24 horas)
        $recentOrders = Order::where('created_at', '>=', now()->subDay())
            ->with('user')
            ->latest()
            ->take(5)
            ->get();
            
        foreach ($recentOrders as $order) {
            $notifications[] = [
                'type' => 'sale',
                'title' => 'Nova Venda',
                'message' => "Venda de R$ " . number_format($order->total_amount, 2, ',', '.') . " para " . $order->user->name,
                'url' => route('admin.orders.show', $order->id),
                'time' => $order->created_at->diffForHumans(),
                'icon' => 'fas fa-shopping-cart',
                'color' => 'success'
            ];
        }
        
        // Notificações de estoque baixo
        $lowStockProducts = Product::where('stock_quantity', '<', 20)
            ->where('active', true)
            ->with('category')
            ->take(10)
            ->get();
            
        foreach ($lowStockProducts as $product) {
            $notifications[] = [
                'type' => 'low_stock',
                'title' => 'Estoque Baixo',
                'message' => $product->name . " tem apenas " . $product->stock_quantity . " unidades",
                'url' => route('admin.products.show', $product->id),
                'time' => 'Agora',
                'icon' => 'fas fa-exclamation-triangle',
                'color' => 'warning'
            ];
        }
        
        // Ordenar por tipo (vendas primeiro) e depois por data
        usort($notifications, function($a, $b) {
            if ($a['type'] === 'sale' && $b['type'] === 'low_stock') {
                return -1;
            }
            if ($a['type'] === 'low_stock' && $b['type'] === 'sale') {
                return 1;
            }
            return 0;
        });
        
        // Limitar a 10 notificações
        $notifications = array_slice($notifications, 0, 10);
        
        return response()->json([
            'notifications' => $notifications,
            'count' => count($notifications)
        ]);
    }
}
