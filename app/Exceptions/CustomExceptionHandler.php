<?php

namespace App\Exceptions;

use App\Exceptions\Auth\AuthException;
use App\Exceptions\Cart\CartException;
use App\Exceptions\Product\ProductException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class CustomExceptionHandler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
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
            // Log específico para exceções de carrinho
            if ($e instanceof CartException) {
                Log::channel('cart')->error('Cart Exception: ' . $e->getMessage(), [
                    'exception' => $e,
                    'user_id' => Auth::id(),
                    'url' => request()->url(),
                    'user_agent' => request()->userAgent(),
                ]);
                return false; // Não reportar para o handler padrão
            }

            // Log específico para exceções de produto
            if ($e instanceof ProductException) {
                Log::channel('products')->error('Product Exception: ' . $e->getMessage(), [
                    'exception' => $e,
                    'user_id' => Auth::id(),
                    'url' => request()->url(),
                ]);
                return false;
            }

            // Log específico para exceções de autenticação
            if ($e instanceof AuthException) {
                Log::channel('auth')->warning('Auth Exception: ' . $e->getMessage(), [
                    'exception' => $e,
                    'ip' => request()->ip(),
                    'url' => request()->url(),
                    'user_agent' => request()->userAgent(),
                ]);
                return false;
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): Response
    {
        // Renderizar exceções customizadas usando seus próprios métodos render
        if ($e instanceof CartException || $e instanceof ProductException || $e instanceof AuthException) {
            $response = $e->render($request);
            if ($response) {
                return $response;
            }
        }

        // Tratamento específico para ModelNotFoundException
        if ($e instanceof ModelNotFoundException) {
            return $this->handleModelNotFoundException($request, $e);
        }

        // Tratamento específico para NotFoundHttpException
        if ($e instanceof NotFoundHttpException) {
            return $this->handleNotFoundHttpException($request, $e);
        }

        // Tratamento específico para ValidationException
        if ($e instanceof ValidationException) {
            return $this->handleValidationException($request, $e);
        }

        // Tratamento específico para UnauthorizedHttpException
        if ($e instanceof UnauthorizedHttpException) {
            return $this->handleUnauthorizedException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle ModelNotFoundException
     */
    protected function handleModelNotFoundException(Request $request, ModelNotFoundException $e): Response
    {
        $model = class_basename($e->getModel());
        $message = "Recurso não encontrado: {$model}";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error_type' => 'not_found',
                'message' => $message,
                'model' => $model
            ], 404);
        }

        return redirect()->back()
            ->with('error', $message)
            ->withInput();
    }

    /**
     * Handle NotFoundHttpException
     */
    protected function handleNotFoundHttpException(Request $request, NotFoundHttpException $e): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error_type' => 'page_not_found',
                'message' => 'Página não encontrada'
            ], 404);
        }

        return response()->view('errors.404', [], 404);
    }

    /**
     * Handle ValidationException
     */
    protected function handleValidationException(Request $request, ValidationException $e): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error_type' => 'validation_error',
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        }

        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput()
            ->with('error', 'Por favor, corrija os erros abaixo');
    }

    /**
     * Handle UnauthorizedHttpException
     */
    protected function handleUnauthorizedException(Request $request, UnauthorizedHttpException $e): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error_type' => 'unauthorized',
                'message' => 'Acesso não autorizado',
                'redirect_to' => route('login')
            ], 401);
        }

        return redirect()->route('login')
            ->with('error', 'Você precisa estar logado para acessar esta página');
    }

    /**
     * Get the context information for logging
     */
    protected function getExceptionContext(Throwable $e): array
    {
        return [
            'exception_class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ];
    }
}
