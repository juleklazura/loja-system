<?php

namespace App\Exceptions\Product;

class ProductNotFoundException extends ProductException
{
    protected $statusCode = 404;

    public function __construct(int $productId = null)
    {
        $message = $productId 
            ? "Produto #{$productId} não encontrado"
            : 'Produto não encontrado';
            
        parent::__construct($message, 3001);
    }
}
