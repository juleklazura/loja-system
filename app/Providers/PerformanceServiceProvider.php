<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class PerformanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Configurar cache padrões para a aplicação
        $this->app->singleton('cart.cache', function ($app) {
            return new \stdClass();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Otimizações de performance para produção
        if ($this->app->environment('production')) {
            // Prevenir lazy loading desnecessário
            Model::preventLazyLoading();
            
            // Configurar limites de query para evitar consultas N+1
            DB::listen(function ($query) {
                if ($query->time > 1000) { // Log consultas que demoram mais que 1 segundo
                    logger()->warning('Slow query detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms'
                    ]);
                }
            });
        }

        // Configurações de cache global
        $this->configureCacheDefaults();
        
        // Configurar otimizações de views
        $this->configureViewOptimizations();
    }

    /**
     * Configurar padrões de cache para a aplicação
     */
    protected function configureCacheDefaults(): void
    {
        // Definir TTLs padrões para diferentes tipos de dados
        config([
            'cache.ttl.cart' => 300,        // 5 minutos
            'cache.ttl.wishlist' => 300,    // 5 minutos
            'cache.ttl.products' => 3600,   // 1 hora
            'cache.ttl.categories' => 7200, // 2 horas
            'cache.ttl.promotions' => 1800, // 30 minutos
        ]);
    }

    /**
     * Configurar otimizações de views
     */
    protected function configureViewOptimizations(): void
    {
        // Compartilhar dados comuns com todas as views para evitar consultas repetidas
        view()->composer('*', function ($view) {
            // Cache de configurações comuns
            $commonData = Cache::remember('common_view_data', 3600, function () {
                return [
                    'app_name' => config('app.name'),
                    'current_year' => date('Y'),
                ];
            });
            
            $view->with($commonData);
        });

        // Composer específico para layout frontend
        view()->composer('layouts.frontend', function ($view) {
            if (Auth::check()) {
                // Dados do usuário logado com cache
                $userData = Cache::remember('user_nav_data_' . Auth::id(), 300, function () {
                    return [
                        'cart_quantity' => \App\Models\CartItem::getUserCartQuantity(Auth::id()),
                        'wishlist_count' => \App\Models\Wishlist::getUserWishlistCount(Auth::id()),
                    ];
                });
                
                $view->with($userData);
            }
        });
    }
}
