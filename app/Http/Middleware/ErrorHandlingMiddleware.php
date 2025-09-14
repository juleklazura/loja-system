<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Global Error Handling Middleware
 * 
 * WHY: Provides comprehensive error handling and recovery mechanisms
 * Catches and gracefully handles various types of errors that may occur
 */
class ErrorHandlingMiddleware
{
    /**
     * Handle an incoming request with comprehensive error catching
     * 
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Attempt to process the request normally
            $response = $next($request);
            
            // Log successful requests for monitoring
            if (app()->environment('production')) {
                $this->logRequestSuccess($request, $response);
            }
            
            return $response;
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors gracefully
            return $this->handleValidationError($request, $e);
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors
            return $this->handleDatabaseError($request, $e);
            
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            // Handle 404 errors
            return $this->handleNotFoundError($request, $e);
            
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            // Handle HTTP exceptions
            return $this->handleHttpError($request, $e);
            
        } catch (\Exception $e) {
            // Handle any other unexpected errors
            return $this->handleGeneralError($request, $e);
        }
    }
    
    /**
     * Handle validation errors with user-friendly messages
     * WHY: Provides clear feedback for validation failures
     */
    private function handleValidationError(Request $request, \Illuminate\Validation\ValidationException $e): Response
    {
        Log::info('Validation error occurred', [
            'url' => $request->fullUrl(),
            'input' => $request->except(['password', 'password_confirmation']),
            'errors' => $e->errors(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos fornecidos',
                'errors' => $e->errors(),
                'error_code' => 'VALIDATION_FAILED'
            ], 422);
        }
        
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput($request->except(['password', 'password_confirmation']))
            ->with('error', 'Por favor, corrija os erros nos campos destacados.');
    }
    
    /**
     * Handle database errors with appropriate fallbacks
     * WHY: Prevents database errors from crashing the application
     */
    private function handleDatabaseError(Request $request, \Illuminate\Database\QueryException $e): Response
    {
        Log::error('Database error occurred', [
            'url' => $request->fullUrl(),
            'sql' => $e->getSql(),
            'bindings' => $e->getBindings(),
            'error_message' => $e->getMessage(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        // Check for specific database error types
        $errorCode = $e->getCode();
        $errorMessage = 'Erro temporário no sistema. Tente novamente em alguns instantes.';
        
        // Handle specific database errors
        if (str_contains($e->getMessage(), 'Duplicate entry')) {
            $errorMessage = 'Este item já existe no sistema.';
        } elseif (str_contains($e->getMessage(), 'foreign key constraint')) {
            $errorMessage = 'Não é possível realizar esta operação devido a dependências.';
        } elseif (str_contains($e->getMessage(), 'Connection refused')) {
            $errorMessage = 'Serviço temporariamente indisponível. Tente novamente em alguns minutos.';
        }
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error_code' => 'DATABASE_ERROR',
                'retry_after' => 30
            ], 500);
        }
        
        return redirect()->back()
            ->with('error', $errorMessage)
            ->withInput($request->except(['password', 'password_confirmation']));
    }
    
    /**
     * Handle 404 errors with helpful suggestions
     * WHY: Provides better user experience for missing resources
     */
    private function handleNotFoundError(Request $request, \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e): Response
    {
        Log::warning('404 error occurred', [
            'url' => $request->fullUrl(),
            'referer' => $request->header('referer'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso não encontrado',
                'error_code' => 'NOT_FOUND',
                'suggestions' => $this->getSuggestions($request)
            ], 404);
        }
        
        // Redirect to appropriate fallback page
        if (str_contains($request->path(), 'admin')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Página não encontrada. Você foi redirecionado para o painel administrativo.');
        } elseif (str_contains($request->path(), 'products')) {
            return redirect()->route('products.index')
                ->with('error', 'Produto não encontrado. Veja nossa lista completa de produtos.');
        }
        
        return redirect()->route('home')
            ->with('error', 'Página não encontrada. Você foi redirecionado para a página inicial.');
    }
    
    /**
     * Handle HTTP errors with appropriate responses
     * WHY: Provides consistent error handling for HTTP exceptions
     */
    private function handleHttpError(Request $request, \Symfony\Component\HttpKernel\Exception\HttpException $e): Response
    {
        Log::warning('HTTP error occurred', [
            'status_code' => $e->getStatusCode(),
            'message' => $e->getMessage(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        $statusCode = $e->getStatusCode();
        $message = $this->getHttpErrorMessage($statusCode);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => 'HTTP_ERROR_' . $statusCode,
                'status_code' => $statusCode
            ], $statusCode);
        }
        
        return redirect()->back()
            ->with('error', $message);
    }
    
    /**
     * Handle general errors with comprehensive logging
     * WHY: Catches any unexpected errors and provides graceful fallback
     */
    private function handleGeneralError(Request $request, \Exception $e): Response
    {
        Log::error('Unexpected error occurred', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'url' => $request->fullUrl(),
            'input' => $request->except(['password', 'password_confirmation']),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        $message = app()->environment('production') 
            ? 'Ocorreu um erro inesperado. Nossa equipe foi notificada e está trabalhando para resolver.'
            : $e->getMessage();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => 'UNEXPECTED_ERROR',
                'debug_info' => app()->environment('production') ? null : [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace()
                ]
            ], 500);
        }
        
        return redirect()->back()
            ->with('error', $message)
            ->withInput($request->except(['password', 'password_confirmation']));
    }
    
    /**
     * Log successful requests for monitoring
     * WHY: Helps with performance monitoring and debugging
     */
    private function logRequestSuccess(Request $request, Response $response): void
    {
        // Only log important endpoints to avoid spam
        $importantEndpoints = [
            'login', 'logout', 'register', 'cart', 'checkout', 'orders'
        ];
        
        $shouldLog = false;
        foreach ($importantEndpoints as $endpoint) {
            if (str_contains($request->path(), $endpoint)) {
                $shouldLog = true;
                break;
            }
        }
        
        if ($shouldLog) {
            Log::info('Request processed successfully', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'response_time' => microtime(true) - LARAVEL_START,
                'ip' => $request->ip()
            ]);
        }
    }
    
    /**
     * Get user-friendly HTTP error messages
     * WHY: Provides better UX with clear error explanations
     */
    private function getHttpErrorMessage(int $statusCode): string
    {
        return match($statusCode) {
            400 => 'Solicitação inválida. Verifique os dados enviados.',
            401 => 'Acesso não autorizado. Faça login para continuar.',
            403 => 'Você não tem permissão para acessar este recurso.',
            404 => 'A página ou recurso solicitado não foi encontrado.',
            405 => 'Método não permitido para esta operação.',
            422 => 'Os dados fornecidos são inválidos.',
            429 => 'Muitas solicitações. Tente novamente em alguns minutos.',
            500 => 'Erro interno do servidor. Tente novamente mais tarde.',
            502 => 'Serviço temporariamente indisponível.',
            503 => 'Serviço em manutenção. Tente novamente em alguns minutos.',
            default => 'Ocorreu um erro inesperado. Tente novamente.'
        };
    }
    
    /**
     * Get helpful suggestions for 404 errors
     * WHY: Helps users find what they're looking for
     */
    private function getSuggestions(Request $request): array
    {
        $path = $request->path();
        $suggestions = [];
        
        if (str_contains($path, 'product')) {
            $suggestions[] = 'Explore nossa lista completa de produtos';
            $suggestions[] = 'Use a busca para encontrar produtos específicos';
        } elseif (str_contains($path, 'category')) {
            $suggestions[] = 'Veja todas as categorias disponíveis';
        } elseif (str_contains($path, 'admin')) {
            $suggestions[] = 'Acesse o painel administrativo';
            $suggestions[] = 'Verifique suas permissões de acesso';
        } else {
            $suggestions[] = 'Volte à página inicial';
            $suggestions[] = 'Use o menu de navegação';
        }
        
        return $suggestions;
    }
}
