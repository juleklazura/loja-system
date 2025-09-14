<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\PromotionController as AdminPromotionController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UsuarioController as AdminUserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\LogViewerController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
Route::get('/produto/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/categoria/{category}', [ProductController::class, 'category'])->name('products.category');

// Dashboard Route (redirect based on user type)
Route::get('/dashboard', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('home');
        }
    }
    return redirect()->route('login');
})->name('dashboard');

// Authenticated User Routes
Route::middleware('auth')->group(function () {
    // Cart Routes with custom auth middleware
    Route::middleware('require.auth')->group(function () {
        Route::get('/carrinho', [CartController::class, 'index'])->name('cart.index');
        Route::post('/carrinho/adicionar', [CartController::class, 'add'])->name('cart.add');
        Route::patch('/carrinho/{cartItem}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/carrinho/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
        Route::delete('/carrinho', [CartController::class, 'clear'])->name('cart.clear');
    });
    
    // Wishlist Routes
    Route::middleware(['throttle:60,1'])->group(function () {
        Route::get('/lista-desejos', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/lista-desejos/adicionar', [WishlistController::class, 'add'])->name('wishlist.add');
        Route::delete('/lista-desejos/{product}', [WishlistController::class, 'remove'])->name('wishlist.remove');
        Route::post('/lista-desejos/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
        Route::post('/lista-desejos/toggle-simple', [WishlistController::class, 'toggleSimple'])->name('wishlist.toggle-simple');
    });
    
    // Checkout Routes
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/pedido/{order}/sucesso', [CheckoutController::class, 'success'])->name('checkout.success');
    
    // User Account Routes
    Route::get('/meus-pedidos', [CheckoutController::class, 'orders'])->name('user.orders');
    Route::get('/pedido/{order}', [CheckoutController::class, 'orderDetail'])->name('user.order.detail');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Category Management
    Route::resource('categories', CategoryController::class);
    
    // Product Management
    Route::resource('products', AdminProductController::class);
    
    // Promotion Management
    Route::resource('promotions', AdminPromotionController::class);
    
    // Order Management
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'destroy']);
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    
    // User Management
    Route::resource('users', AdminUserController::class)->except(['create', 'store']);
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    
    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/backup', [SettingController::class, 'backup'])->name('settings.backup');
    Route::post('/settings/clear-cache', [SettingController::class, 'clearCache'])->name('settings.clearCache');
    Route::post('/settings/test-email', [SettingController::class, 'testEmail'])->name('settings.testEmail');
    Route::get('/settings/system-info', [SettingController::class, 'systemInfo'])->name('settings.systemInfo');
    
    // Log Viewer
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [LogViewerController::class, 'index'])->name('index');
        Route::get('/stats', [LogViewerController::class, 'stats'])->name('stats');
        Route::get('/search', [LogViewerController::class, 'search'])->name('search');
        Route::get('/{filename}', [LogViewerController::class, 'show'])->name('show');
        Route::get('/{filename}/download', [LogViewerController::class, 'download'])->name('download');
        Route::post('/{filename}/clear', [LogViewerController::class, 'clear'])->name('clear');
        Route::delete('/{filename}', [LogViewerController::class, 'delete'])->name('delete');
    });
    
    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'getNotifications'])->name('notifications.get');
});

require __DIR__.'/auth.php';
