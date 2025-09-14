<?php

namespace App\Exceptions\Cart;

class CartItemNotFoundException extends CartException
{
    protected $statusCode = 404;

    public function __construct(int $cartItemId = null)
    {
        $message = $cartItemId 
            ? "Item do carrinho #{$cartItemId} não encontrado"
            : 'Item do carrinho não encontrado';
            
        parent::__construct($message, 1002);
    }
}
