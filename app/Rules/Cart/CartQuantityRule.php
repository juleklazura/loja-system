<?php

namespace App\Rules\Cart;

use Illuminate\Contracts\Validation\Rule;

class CartQuantityRule implements Rule
{
    private int $maxQuantity;

    public function __construct(int $maxQuantity = null)
    {
        $this->maxQuantity = $maxQuantity ?? config('cart.max_quantity', 99);
    }

    public function passes($attribute, $value)
    {
        return is_numeric($value) 
            && $value > 0 
            && $value <= $this->maxQuantity;
    }

    public function message()
    {
        return "A quantidade deve estar entre 1 e {$this->maxQuantity}.";
    }
}
