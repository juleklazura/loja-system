<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class CartRateLimitMiddleware
{
    private const CART_ADD_RATE_LIMIT = 10; // 10 tentativas por minuto
    private const CART_UPDATE_RATE_LIMIT = 20; // 20 atualizações por minuto
    private const CART_REMOVE_RATE_LIMIT = 15; // 15 remoções por minuto
    
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $operation = 'general'): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $key = $this->buildRateLimitKey($user->id, $operation);
        $limit = $this->getOperationLimit($operation);

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'message' => 'Muitas tentativas. Tente novamente em ' . $seconds . ' segundos.',
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, 60); // TTL de 60 segundos

        $response = $next($request);

        // Reset rate limit em caso de sucesso para não penalizar operações válidas
        if ($response->getStatusCode() === 200) {
            $this->handleSuccessfulOperation($key, $operation);
        }

        return $response;
    }

    private function buildRateLimitKey(int $userId, string $operation): string
    {
        return "cart_rate_limit:{$userId}:{$operation}";
    }

    private function getOperationLimit(string $operation): int
    {
        return match ($operation) {
            'add' => self::CART_ADD_RATE_LIMIT,
            'update' => self::CART_UPDATE_RATE_LIMIT,
            'remove' => self::CART_REMOVE_RATE_LIMIT,
            default => 30 // Limite geral mais alto
        };
    }

    private function handleSuccessfulOperation(string $key, string $operation): void
    {
        // Para operações de adicionar, podemos ser mais restritivos
        if ($operation === 'add') {
            // Mantém o rate limit para adicionar produtos
            return;
        }

        // Para outras operações, podemos ser mais flexíveis
        $attempts = RateLimiter::attempts($key);
        if ($attempts > 0) {
            RateLimiter::clear($key);
        }
    }
}
