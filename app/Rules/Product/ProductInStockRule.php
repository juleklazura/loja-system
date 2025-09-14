<?php

namespace App\Rules\Product;

use App\Models\Product;
use Illuminate\Contracts\Validation\Rule;

class ProductInStockRule implements Rule
{
    private int $requestedQuantity;

    public function __construct(int $requestedQuantity = 1)
    {
        $this->requestedQuantity = $requestedQuantity;
    }

    public function passes($attribute, $value)
    {
        $product = Product::find($value);
        return $product && $product->stock_quantity >= $this->requestedQuantity;
    }

    public function message()
    {
        return 'Não há estoque suficiente para a quantidade solicitada.';
    }

    public function setRequestedQuantity(int $quantity): self
    {
        $this->requestedQuantity = $quantity;
        return $this;
    }
}
