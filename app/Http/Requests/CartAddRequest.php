<?php

namespace App\Http\Requests;

use App\Rules\Cart\CartQuantityRule;
use App\Rules\Product\ProductActiveRule;
use App\Rules\Product\ProductInStockRule;
use Illuminate\Foundation\Http\FormRequest;

class CartAddRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::check();
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
                'min:1',
                new ProductActiveRule(),
                new ProductInStockRule($this->input('quantity', 1))
            ],
            'quantity' => [
                'required',
                'integer',
                new CartQuantityRule()
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'O produto é obrigatório.',
            'product_id.integer' => 'ID do produto deve ser um número inteiro.',
            'product_id.exists' => 'Produto não encontrado.',
            'product_id.min' => 'ID do produto inválido.',
            'quantity.required' => 'A quantidade é obrigatória.',
            'quantity.integer' => 'A quantidade deve ser um número inteiro.',
            'quantity.min' => 'A quantidade mínima é 1.',
            'quantity.max' => 'A quantidade máxima é 100.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'product_id' => (int) $this->product_id,
            'quantity' => (int) $this->quantity,
        ]);
    }
}
