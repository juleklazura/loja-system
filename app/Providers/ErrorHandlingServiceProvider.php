<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use App\Exceptions\Handler;

/**
 * Error Handling Service Provider
 * 
 * WHY: Registers custom error handling services and configurations
 */
class ErrorHandlingServiceProvider extends ServiceProvider
{
    /**
     * Register error handling services.
     */
    public function register()
    {
        // Register custom exception handler
        $this->app->singleton(
            ExceptionHandler::class,
            Handler::class
        );
    }

    /**
     * Bootstrap error handling services.
     */
    public function boot()
    {
        // Configure global error handling behavior
        $this->configureErrorReporting();
        $this->configureMonitoring();
    }
    
    /**
     * Configure error reporting based on environment
     */
    private function configureErrorReporting()
    {
        if ($this->app->environment('production')) {
            // Production: Hide errors from users
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
        } else {
            // Development: Show all errors
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
            error_reporting(E_ALL);
        }
    }
    
    /**
     * Configure error monitoring
     */
    private function configureMonitoring()
    {
        // Set custom error handler for PHP errors
        set_error_handler([$this, 'handlePhpError']);
        
        // Set custom exception handler for uncaught exceptions
        set_exception_handler([$this, 'handleUncaughtException']);
        
        // Set custom handler for fatal errors
        register_shutdown_function([$this, 'handleFatalError']);
    }
    
    /**
     * Handle PHP errors with comprehensive logging
     */
    public function handlePhpError($severity, $message, $filename, $lineno)
    {
        // Don't handle errors that should be ignored
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        try {
            $context = [
                'severity' => $this->getSeverityName($severity),
                'message' => $message,
                'file' => $filename,
                'line' => $lineno,
                'url' => request() ? request()->fullUrl() : 'N/A',
                'user_agent' => request() ? request()->userAgent() : 'N/A'
            ];
            
            if ($severity & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_PARSE)) {
                \Illuminate\Support\Facades\Log::critical('PHP Fatal Error', $context);
            } elseif ($severity & (E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING)) {
                \Illuminate\Support\Facades\Log::warning('PHP Warning', $context);
            } else {
                \Illuminate\Support\Facades\Log::notice('PHP Notice', $context);
            }
            
        } catch (\Exception $e) {
            error_log("Error handling PHP error: " . $e->getMessage());
        }
        
        // Return false to let PHP's internal error handler continue
        return false;
    }
    
    /**
     * Handle uncaught exceptions
     */
    public function handleUncaughtException(\Throwable $exception)
    {
        try {
            \Illuminate\Support\Facades\Log::critical('Uncaught Exception', [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
        } catch (\Exception $e) {
            error_log("Error handling uncaught exception: " . $e->getMessage());
        }
    }
    
    /**
     * Handle fatal errors
     */
    public function handleFatalError()
    {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            try {
                \Illuminate\Support\Facades\Log::critical('PHP Fatal Error', [
                    'type' => $this->getSeverityName($error['type']),
                    'message' => $error['message'],
                    'file' => $error['file'],
                    'line' => $error['line']
                ]);
            } catch (\Exception $e) {
                error_log("Error handling fatal error: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Get human-readable severity name
     */
    private function getSeverityName($severity)
    {
        $levels = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];
        
        return $levels[$severity] ?? 'Unknown Error';
    }
}
