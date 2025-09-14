<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se o usuário está autenticado
        if (!Auth::check()) {
            // Se a requisição espera JSON (API), retornar resposta JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado. Você precisa estar logado.',
                    'redirect_to' => route('login')
                ], 401);
            }
            
            // Para requisições web, redirecionar para login
            return redirect()->guest(route('login'))
                ->with('error', 'Você precisa estar logado para acessar esta página.');
        }

        return $next($request);
    }
}
