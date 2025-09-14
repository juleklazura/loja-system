<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLoggingMiddleware
{
    public function __construct(
        private AuditService $auditService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Log da requisição de entrada
        $this->logIncomingRequest($request);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // em milissegundos
        
        // Log da resposta
        $this->logResponse($request, $response, $executionTime);
        
        return $response;
    }

    /**
     * Log da requisição de entrada
     */
    private function logIncomingRequest(Request $request): void
    {
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $this->sanitizeHeaders($request->headers->all()),
            'parameters' => $this->sanitizeParameters($request->all()),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'timestamp' => now()->toISOString(),
        ];

        Log::info('INCOMING REQUEST', $logData);
    }

    /**
     * Log da resposta
     */
    private function logResponse(Request $request, Response $response, float $executionTime): void
    {
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'execution_time' => $executionTime,
            'memory_usage' => memory_get_usage(true),
            'user_id' => Auth::id(),
            'timestamp' => now()->toISOString(),
        ];

        $level = $this->getLogLevelFromStatusCode($response->getStatusCode());
        Log::$level('REQUEST COMPLETED', $logData);

        // Log de performance para requisições lentas
        if ($executionTime > 1000) { // Mais de 1 segundo
            $this->auditService->logPerformance(
                'slow_request',
                $executionTime,
                [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ]
            );
        }
    }

    /**
     * Sanitizar headers sensíveis
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'cookie',
            'x-api-key',
            'x-auth-token',
        ];

        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['***REDACTED***'];
            }
        }

        return $headers;
    }

    /**
     * Sanitizar parâmetros sensíveis
     */
    private function sanitizeParameters(array $parameters): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'api_key',
            'credit_card',
            'cvv',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($parameters[$field])) {
                $parameters[$field] = '***REDACTED***';
            }
        }

        return $parameters;
    }

    /**
     * Determinar nível de log baseado no status code
     */
    private function getLogLevelFromStatusCode(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return 'error';
        }
        
        if ($statusCode >= 400) {
            return 'warning';
        }
        
        return 'info';
    }
}
