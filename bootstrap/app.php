<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'require.auth' => \App\Http\Middleware\RequireAuthMiddleware::class,
            'log.requests' => \App\Http\Middleware\RequestLoggingMiddleware::class,
            'cart.rate.limit' => \App\Http\Middleware\CartRateLimitMiddleware::class,
        ]);
        
        // Middleware global para logging de requisições (opcional)
        // $middleware->append(\App\Http\Middleware\RequestLoggingMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Registrar callbacks para exceções específicas
        $exceptions->render(function (\App\Exceptions\Cart\CartException $e, $request) {
            return $e->render($request);
        });
        
        $exceptions->render(function (\App\Exceptions\Product\ProductException $e, $request) {
            return $e->render($request);
        });
        
        $exceptions->render(function (\App\Exceptions\Auth\AuthException $e, $request) {
            return $e->render($request);
        });
    })->create();
