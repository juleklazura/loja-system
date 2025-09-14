<?php

namespace App\Exceptions\Cart;

class InvalidQuantityException extends CartException
{
    protected $statusCode = 422;

    public function __construct(int $quantity = 0, int $maxQuantity = 0)
    {
        $message = "Quantidade inválida: {$quantity}. ";
        
        if ($maxQuantity > 0) {
            $message .= "Quantidade máxima permitida: {$maxQuantity}";
        } else {
            $message .= "A quantidade deve ser maior que zero";
        }
            
        parent::__construct($message, 1003);
    }
}
