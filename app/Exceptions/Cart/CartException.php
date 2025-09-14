<?php

namespace App\Exceptions\Cart;

use Exception;

class CartException extends Exception
{
    protected $statusCode = 400;

    public function __construct(string $message = 'Erro no carrinho', int $code = 0, ?Exception $previous = null)
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
                'error_type' => 'cart_error',
                'message' => $this->getMessage(),
                'code' => $this->getCode()
            ], $this->getStatusCode());
        }

        return redirect()->back()
            ->with('error', $this->getMessage())
            ->withInput();
    }
}
