<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class AuditService
{
    /**
     * Não injetar Request no construtor para evitar dependência circular
     * Request será obtido via helper request() quando necessário
     */
    public function __construct(
        private ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: Log::getFacadeRoot();
    }

    /**
     * Log de ação do usuário
     */
    public function logUserAction(string $action, string $description, array $data = [], string $level = 'info'): void
    {
        if (!config('audit.enabled', true)) {
            return;
        }

        $logData = $this->buildBaseLogData($action, $description, $data);
        Log::channel('audit')->{$level}($description, $logData);
    }

    /**
     * Obter dados da request de forma segura
     */
    private function getRequestData(): array
    {
        $request = request();
        
        if (!$request) {
            return [
                'ip_address' => null,
                'url' => null,
                'method' => null,
                'user_agent' => null,
            ];
        }

        return [
            'ip_address' => $request->ip(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_agent' => $request->userAgent(),
        ];
    }

    /**
     * Log de ação do administrador
     */
    public function logAdminAction(string $action, string $description, array $data = []): void
    {
        if (!config('audit.enabled', true)) {
            return;
        }

        $logData = $this->buildBaseLogData($action, $description, $data);
        $logData['user_type'] = 'admin';
        Log::channel('audit')->warning($description, $logData);
    }

    /**
     * Log de evento de segurança
     */
    public function logSecurityEvent(string $event, string $description, array $data = [], string $level = 'warning'): void
    {
        if (!config('audit.enabled', true)) {
            return;
        }

        $requestData = $this->getRequestData();
        $logData = array_merge($this->buildBaseLogData($event, $description, $data), $requestData);
        Log::channel('security')->{$level}($description, $logData);
    }

    /**
     * Log de performance (apenas se lenta)
     */
    public function logPerformance(string $operation, float $executionTime, array $data = []): void
    {
        $threshold = config('audit.slow_request_threshold', 500);
        
        if ($executionTime < $threshold) {
            return;
        }

        $requestData = $this->getRequestData();
        
        $logData = [
            'operation' => $operation,
            'execution_time' => $executionTime,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'user_id' => Auth::id(),
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];

        $logData = array_merge($logData, $requestData);
        Log::channel('performance')->warning("Slow operation: {$operation}", $logData);
    }

    /**
     * Log de ação do carrinho
     */
    public function logCartAction(string $action, array $data = []): void
    {
        if (!config('audit.log_cart_actions', true)) {
            return;
        }

        $logData = $this->buildBaseLogData("cart.{$action}", "Ação do carrinho: {$action}", $data);
        Log::channel('cart')->info("Cart action: {$action}", $logData);
    }

    /**
     * Log de ação do produto
     */
    public function logProductAction(string $action, array $data = []): void
    {
        if (!config('audit.log_product_actions', true)) {
            return;
        }

        $logData = $this->buildBaseLogData("product.{$action}", "Ação do produto: {$action}", $data);
        Log::channel('products')->info("Product action: {$action}", $logData);
    }

    /**
     * Log de ação de autenticação
     */
    public function logAuthAction(string $action, array $data = []): void
    {
        if (!config('audit.enabled', true)) {
            return;
        }

        $requestData = $this->getRequestData();
        $logData = array_merge($this->buildBaseLogData("auth.{$action}", "Ação de autenticação: {$action}", $data), $requestData);
        Log::channel('auth')->info("Auth action: {$action}", $logData);
    }

    /**
     * Log de erro
     */
    public function logError(\Throwable $exception, string $context = '', array $additionalData = []): void
    {
        if (!config('audit.enabled', true)) {
            return;
        }

        $requestData = $this->getRequestData();
        
        $logData = array_merge([
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'context' => $context,
            'user_id' => Auth::id(),
            'timestamp' => now()->toISOString(),
        ], $requestData, $additionalData);

        $this->logger->error("ERROR: {$exception->getMessage()}", $logData);
    }

    /**
     * Log de query do banco de dados
     */
    public function logDatabaseQuery(string $query, array $bindings, float $time): void
    {
        $threshold = config('audit.slow_query_threshold', 100); // 100ms
        
        if ($time < $threshold || !config('audit.enabled', true)) {
            return;
        }

        $logData = [
            'query' => $query,
            'bindings' => $bindings,
            'execution_time' => $time,
            'user_id' => Auth::id(),
            'timestamp' => now()->toISOString(),
        ];

        Log::channel('database')->warning("Slow query detected", $logData);
    }

    /**
     * Log de requisição API
     */
    public function logApiRequest(string $endpoint, array $requestData, array $responseData, int $statusCode, float $responseTime): void
    {
        if (!config('audit.enabled', true)) {
            return;
        }

        $requestInfo = $this->getRequestData();
        
        $logData = array_merge([
            'endpoint' => $endpoint,
            'request_data' => $this->sanitizeData($requestData),
            'response_data' => $responseData,
            'status_code' => $statusCode,
            'response_time' => $responseTime,
            'user_id' => Auth::id(),
            'timestamp' => now()->toISOString(),
        ], $requestInfo);

        $level = $statusCode >= 400 ? 'error' : 'info';
        Log::channel('api')->{$level}("API Request: {$endpoint}", $logData);
    }

    /**
     * Construir dados base do log
     */
    private function buildBaseLogData(string $action, string $description, array $data): array
    {
        return [
            'action' => $action,
            'description' => $description,
            'data' => $this->sanitizeData($data),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Sanitizar dados sensíveis
     */
    private function sanitizeData(array $data): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'api_key', 'credit_card', 'cvv'];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '***REDACTED***';
            }
        }
        
        return $data;
    }
}
