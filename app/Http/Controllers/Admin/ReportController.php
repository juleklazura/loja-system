<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display reports dashboard.
     */
    public function index()
    {
        $startDate = request('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = request('end_date', now()->format('Y-m-d'));

        // Sales statistics
        $salesStats = $this->getSalesStats($startDate, $endDate);
        
        // Product statistics
        $productStats = $this->getProductStats($startDate, $endDate);
        
        // Customer statistics
        $customerStats = $this->getCustomerStats($startDate, $endDate);
        
        // Charts data
        $chartsData = $this->getChartsData($startDate, $endDate);

        return view('admin.reports.index', compact(
            'salesStats', 'productStats', 'customerStats', 'chartsData', 'startDate', 'endDate'
        ));
    }

    private function getSalesStats($startDate, $endDate)
    {
        $baseQuery = Order::whereBetween('created_at', [$startDate, $endDate]);
        
        return [
            'total_orders' => (clone $baseQuery)->count(),
            'total_revenue' => (clone $baseQuery)->sum('total_amount'),
            'average_order' => (clone $baseQuery)->avg('total_amount') ?? 0,
            'pending_orders' => (clone $baseQuery)->where('status', 'pending')->count(),
            'confirmed_orders' => (clone $baseQuery)->where('status', 'confirmed')->count(),
            'delivered_orders' => (clone $baseQuery)->where('status', 'delivered')->count(),
            'cancelled_orders' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];
    }

    private function getProductStats($startDate, $endDate)
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('active', true)->count();
        $lowStockProducts = Product::where('stock_quantity', '<', 20)->count();
        $outOfStockProducts = Product::where('stock_quantity', 0)->count();

        // Best selling products
        $bestSelling = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        return [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'low_stock_products' => $lowStockProducts,
            'out_of_stock_products' => $outOfStockProducts,
            'best_selling' => $bestSelling,
        ];
    }

    private function getCustomerStats($startDate, $endDate)
    {
        $totalCustomers = User::where('type', 'customer')->count();
        $newCustomers = User::where('type', 'customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        // Top customers
        $topCustomers = User::where('type', 'customer')
            ->with(['orders' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get()
            ->map(function($user) {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'total_orders' => $user->orders->count(),
                    'total_spent' => $user->orders->sum('total_amount'),
                ];
            })
            ->sortByDesc('total_spent')
            ->take(10);

        return [
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'top_customers' => $topCustomers,
        ];
    }

    private function getChartsData($startDate, $endDate)
    {
        // Daily sales chart
        $dailySales = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Category sales
        $categorySales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'categories.name',
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as total')
            )
            ->groupBy('categories.id', 'categories.name')
            ->get();

        return [
            'daily_sales' => $dailySales,
            'category_sales' => $categorySales,
        ];
    }

    /**
     * Export reports to CSV.
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'sales');
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        switch ($type) {
            case 'sales':
                return $this->exportSales($startDate, $endDate);
            case 'products':
                return $this->exportProducts($startDate, $endDate);
            case 'customers':
                return $this->exportCustomers($startDate, $endDate);
            default:
                return redirect()->back()->with('error', 'Tipo de relatório inválido.');
        }
    }

    private function exportSales($startDate, $endDate)
    {
        $orders = Order::with(['user', 'items.product'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $filename = "vendas_{$startDate}_a_{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Cliente', 'Email', 'Total', 'Status', 'Data']);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->user->name,
                    $order->user->email,
                    $order->total_amount,
                    $order->status,
                    $order->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportProducts($startDate, $endDate)
    {
        $products = Product::with('category')->get();
        
        $filename = "produtos_{$startDate}_a_{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nome', 'SKU', 'Categoria', 'Preço', 'Estoque', 'Status']);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->sku,
                    $product->category->name ?? 'N/A',
                    $product->price,
                    $product->stock_quantity,
                    $product->active ? 'Ativo' : 'Inativo',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportCustomers($startDate, $endDate)
    {
        $customers = User::where('type', 'customer')
            ->with('orders')
            ->get();
        
        $filename = "clientes_{$startDate}_a_{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nome', 'Email', 'Total de Pedidos', 'Total Gasto', 'Cadastro']);

            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->orders->count(),
                    $customer->orders->sum('total_amount'),
                    $customer->created_at->format('d/m/Y'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
