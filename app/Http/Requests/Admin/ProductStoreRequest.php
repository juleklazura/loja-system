<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::check() && 
               \Illuminate\Support\Facades\Auth::user()->user_type === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\p{N}\s\-\.\,\(\)\_]+$/u'
            ],
            'description' => [
                'required',
                'string',
                'max:5000'
            ],
            'price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'promotional_price' => [
                'nullable',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'lt:price',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'stock_quantity' => [
                'required',
                'integer',
                'min:0',
                'max:999999'
            ],
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
                'min:1'
            ],
            'sku' => [
                'required',
                'string',
                'max:100',
                'unique:products,sku',
                'regex:/^[A-Z0-9\-\_]+$/i'
            ],
            'active' => [
                'boolean'
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do produto é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'name.regex' => 'O nome contém caracteres inválidos.',
            
            'description.required' => 'A descrição é obrigatória.',
            'description.string' => 'A descrição deve ser um texto válido.',
            'description.max' => 'A descrição não pode ter mais de 5000 caracteres.',
            
            'price.required' => 'O preço é obrigatório.',
            'price.numeric' => 'O preço deve ser um valor numérico.',
            'price.min' => 'O preço deve ser maior que zero.',
            'price.max' => 'O preço é muito alto.',
            'price.regex' => 'O preço deve ter no máximo 2 casas decimais.',
            
            'promotional_price.numeric' => 'O preço promocional deve ser um valor numérico.',
            'promotional_price.min' => 'O preço promocional deve ser maior que zero.',
            'promotional_price.max' => 'O preço promocional é muito alto.',
            'promotional_price.lt' => 'O preço promocional deve ser menor que o preço normal.',
            'promotional_price.regex' => 'O preço promocional deve ter no máximo 2 casas decimais.',
            
            'stock_quantity.required' => 'A quantidade em estoque é obrigatória.',
            'stock_quantity.integer' => 'A quantidade deve ser um número inteiro.',
            'stock_quantity.min' => 'A quantidade não pode ser negativa.',
            'stock_quantity.max' => 'Quantidade muito alta.',
            
            'category_id.required' => 'A categoria é obrigatória.',
            'category_id.integer' => 'ID da categoria deve ser um número inteiro.',
            'category_id.exists' => 'Categoria não encontrada.',
            'category_id.min' => 'ID da categoria inválido.',
            
            'sku.required' => 'O SKU é obrigatório.',
            'sku.string' => 'O SKU deve ser um texto válido.',
            'sku.max' => 'O SKU não pode ter mais de 100 caracteres.',
            'sku.unique' => 'Este SKU já está em uso.',
            'sku.regex' => 'O SKU deve conter apenas letras, números, hífens e sublinhados.',
            
            'image.image' => 'O arquivo deve ser uma imagem.',
            'image.mimes' => 'A imagem deve ser JPG, JPEG, PNG ou WebP.',
            'image.max' => 'A imagem não pode ser maior que 2MB.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'category_id' => (int) $this->category_id,
            'stock_quantity' => (int) $this->stock_quantity,
            'price' => (float) $this->price,
            'promotional_price' => $this->promotional_price ? (float) $this->promotional_price : null,
            'active' => $this->boolean('active', true),
        ]);
    }
}
