<?php

namespace App\Exceptions\Cart;

class ProductNotAvailableException extends CartException
{
    protected $statusCode = 422;

    public function __construct(string $productName = '', int $availableStock = 0)
    {
        $message = $productName 
            ? "O produto '{$productName}' não está disponível ou tem estoque insuficiente. Estoque disponível: {$availableStock}"
            : 'Produto não disponível ou estoque insuficiente';
            
        parent::__construct($message, 1001);
    }
}
