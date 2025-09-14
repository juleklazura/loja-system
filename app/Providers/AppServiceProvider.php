<?php

namespace App\Providers;

use App\Services\AuditService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use Illuminate\Pagination\Paginator;
use App\Http\View\Composers\PromotionComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar o AuditService como singleton SEM injeção de Request
        $this->app->singleton(AuditService::class, function ($app) {
            return new AuditService();
        });
        
        // Cache service singleton
        $this->app->singleton(\App\Services\CacheService::class);
        
        // Repository bindings
        $this->app->bind(
            \App\Contracts\CartRepositoryInterface::class,
            \App\Repositories\CartRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure pagination view
        Paginator::defaultView('components.pagination');
        Paginator::defaultSimpleView('components.pagination-simple');
        
        // Register view composer for promotions ticker
        View::composer('layouts.frontend', PromotionComposer::class);
        
        // Register cart events
        $this->registerCartEvents();
    }
    
    /**
     * Register cart events and listeners
     */
    private function registerCartEvents(): void
    {
        Event::listen(\App\Events\Cart\CartItemAdded::class, \App\Listeners\Cart\LogCartItemAdded::class);
        Event::listen(\App\Events\Cart\CartItemUpdated::class, \App\Listeners\Cart\LogCartItemUpdated::class);
        Event::listen(\App\Events\Cart\CartItemRemoved::class, \App\Listeners\Cart\LogCartItemRemoved::class);
        Event::listen(\App\Events\Cart\CartCleared::class, \App\Listeners\Cart\LogCartCleared::class);
    }
}
