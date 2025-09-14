<?php

namespace App\Exceptions\Auth;

use Exception;

class AuthException extends Exception
{
    protected $statusCode = 401;

    public function __construct(string $message = 'Erro de autenticação', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error_type' => 'auth_error',
                'message' => $this->getMessage(),
                'code' => $this->getCode(),
                'redirect_to' => route('login')
            ], $this->getStatusCode());
        }

        return redirect()->route('login')
            ->with('error', $this->getMessage());
    }
}
