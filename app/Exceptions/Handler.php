<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Custom Exception Handler
 * 
 * WHY: Provides centralized exception handling with comprehensive error recovery
 * Ensures all errors are properly logged and users receive appropriate responses
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $this->logError($e);
        });
    }
    
    /**
     * Render an exception into an HTTP response with comprehensive error handling
     * WHY: Ensures users always receive appropriate error responses
     */
    public function render($request, Throwable $e): Response
    {
        try {
            // Handle specific exception types
            if ($e instanceof \Illuminate\Database\QueryException) {
                return $this->handleDatabaseException($request, $e);
            }
            
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return $this->handleValidationException($request, $e);
            }
            
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return $this->handleNotFoundHttpException($request, $e);
            }
            
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return $this->handleAuthenticationException($request, $e);
            }
            
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return $this->handleAuthorizationException($request, $e);
            }
            
            // Handle rate limiting
            if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                return $this->handleThrottleException($request, $e);
            }
            
            // Handle file upload errors
            if ($e instanceof \Illuminate\Http\Exceptions\PostTooLargeException) {
                return $this->handlePostTooLargeException($request, $e);
            }
            
            // Default handling for other exceptions
            return $this->handleGeneralException($request, $e);
            
        } catch (\Exception $renderException) {
            // If rendering fails, log the error and provide minimal response
            Log::emergency('Exception rendering failed', [
                'original_exception' => $e->getMessage(),
                'render_exception' => $renderException->getMessage(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip()
            ]);
            
            return $this->getMinimalErrorResponse($request);
        }
    }
    
    /**
     * Enhanced error logging with context
     * WHY: Provides comprehensive error information for debugging
     */
    private function logError(Throwable $e): void
    {
        try {
            $context = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
            
            // Add request context if available
            if (request()) {
                $context['url'] = request()->fullUrl();
                $context['method'] = request()->method();
                $context['ip'] = request()->ip();
                $context['user_agent'] = request()->userAgent();
                $context['user_id'] = \Illuminate\Support\Facades\Auth::id();
                
                // Add input data (excluding sensitive fields)
                $input = request()->except([
                    'password', 'password_confirmation', 'token', '_token'
                ]);
                if (!empty($input)) {
                    $context['input'] = $input;
                }
            }
            
            // Log with appropriate level based on exception type
            if ($e instanceof \Illuminate\Database\QueryException) {
                Log::error('Database error', $context);
            } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                Log::warning('HTTP exception', $context);
            } else {
                Log::error('Application error', $context);
            }
            
        } catch (\Exception $logException) {
            // If logging fails, try minimal logging
            try {
                Log::emergency('Error logging failed', [
                    'original_error' => $e->getMessage(),
                    'log_error' => $logException->getMessage()
                ]);
            } catch (\Exception $minimalLogException) {
                // If even minimal logging fails, write to error_log
                error_log('Critical: Both error logging and fallback logging failed');
            }
        }
    }
    
    /**
     * Handle database exceptions with appropriate user messages
     */
    private function handleDatabaseException(Request $request, \Illuminate\Database\QueryException $e): Response
    {
        $message = $this->getDatabaseErrorMessage($e);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => 'DATABASE_ERROR'
            ], 500);
        }
        
        return redirect()->back()
            ->with('error', $message)
            ->withInput($request->except($this->dontFlash));
    }
    
    /**
     * Handle validation exceptions with detailed errors
     */
    private function handleValidationException(Request $request, \Illuminate\Validation\ValidationException $e): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados de entrada inválidos',
                'errors' => $e->errors(),
                'error_code' => 'VALIDATION_ERROR'
            ], 422);
        }
        
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput($request->except($this->dontFlash))
            ->with('error', 'Por favor, corrija os erros destacados nos campos.');
    }
    
    /**
     * Handle 404 errors with helpful redirects
     */
    private function handleNotFoundHttpException(Request $request, \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso não encontrado',
                'error_code' => 'NOT_FOUND'
            ], 404);
        }
        
        // Smart redirects based on URL patterns
        $path = $request->path();
        
        if (str_contains($path, 'admin')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Página administrativa não encontrada.');
        }
        
        if (str_contains($path, 'products')) {
            return redirect()->route('products.index')
                ->with('error', 'Produto não encontrado. Veja nossa lista completa.');
        }
        
        return redirect()->route('home')
            ->with('error', 'Página não encontrada.');
    }
    
    /**
     * Handle authentication errors
     */
    private function handleAuthenticationException(Request $request, \Illuminate\Auth\AuthenticationException $e): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado. Faça login para continuar.',
                'error_code' => 'AUTHENTICATION_REQUIRED'
            ], 401);
        }
        
        return redirect()->route('login')
            ->with('error', 'Você precisa fazer login para acessar esta página.');
    }
    
    /**
     * Handle authorization errors
     */
    private function handleAuthorizationException(Request $request, \Illuminate\Auth\Access\AuthorizationException $e): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para realizar esta ação.',
                'error_code' => 'AUTHORIZATION_DENIED'
            ], 403);
        }
        
        return redirect()->back()
            ->with('error', 'Você não tem permissão para realizar esta ação.');
    }
    
    /**
     * Handle throttle/rate limiting errors
     */
    private function handleThrottleException(Request $request, \Illuminate\Http\Exceptions\ThrottleRequestsException $e): Response
    {
        $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Muitas tentativas. Tente novamente em alguns minutos.',
                'error_code' => 'RATE_LIMITED',
                'retry_after' => $retryAfter
            ], 429);
        }
        
        return redirect()->back()
            ->with('error', "Muitas tentativas. Tente novamente em {$retryAfter} segundos.");
    }
    
    /**
     * Handle file upload too large errors
     */
    private function handlePostTooLargeException(Request $request, \Illuminate\Http\Exceptions\PostTooLargeException $e): Response
    {
        $maxSize = ini_get('post_max_size');
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => "Arquivo muito grande. Tamanho máximo permitido: {$maxSize}",
                'error_code' => 'FILE_TOO_LARGE',
                'max_size' => $maxSize
            ], 413);
        }
        
        return redirect()->back()
            ->with('error', "Arquivo muito grande. Tamanho máximo permitido: {$maxSize}")
            ->withInput($request->except($this->dontFlash));
    }
    
    /**
     * Handle general exceptions
     */
    private function handleGeneralException(Request $request, Throwable $e): Response
    {
        $message = app()->environment('production')
            ? 'Ocorreu um erro inesperado. Nossa equipe foi notificada.'
            : $e->getMessage();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => 'GENERAL_ERROR',
                'debug' => app()->environment('production') ? null : [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
        
        return redirect()->back()
            ->with('error', $message)
            ->withInput($request->except($this->dontFlash));
    }
    
    /**
     * Get minimal error response when rendering fails
     */
    private function getMinimalErrorResponse(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro crítico do sistema',
                'error_code' => 'CRITICAL_ERROR'
            ], 500);
        }
        
        // Return basic HTML response
        return response(
            '<h1>Erro do Sistema</h1><p>Ocorreu um erro crítico. Tente novamente mais tarde.</p>',
            500,
            ['Content-Type' => 'text/html']
        );
    }
    
    /**
     * Get user-friendly database error messages
     */
    private function getDatabaseErrorMessage(\Illuminate\Database\QueryException $e): string
    {
        $message = $e->getMessage();
        
        if (str_contains($message, 'Duplicate entry')) {
            return 'Este item já existe no sistema.';
        }
        
        if (str_contains($message, 'foreign key constraint')) {
            return 'Não é possível realizar esta operação devido a dependências.';
        }
        
        if (str_contains($message, 'Connection refused') || str_contains($message, 'Connection timed out')) {
            return 'Problema de conectividade com o banco de dados. Tente novamente em alguns minutos.';
        }
        
        if (str_contains($message, 'Table') && str_contains($message, "doesn't exist")) {
            return 'Erro de configuração do sistema. Contate o administrador.';
        }
        
        return 'Erro temporário no sistema. Tente novamente em alguns instantes.';
    }
}
