<?php

namespace App\Rules\Product;

use App\Models\Product;
use Illuminate\Contracts\Validation\Rule;

class ProductActiveRule implements Rule
{
    public function passes($attribute, $value)
    {
        $product = Product::find($value);
        return $product && $product->active;
    }

    public function message()
    {
        return 'O produto selecionado não está disponível.';
    }
}
