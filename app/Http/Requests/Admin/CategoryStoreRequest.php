<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CategoryStoreRequest extends FormRequest
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
                'unique:categories,name',
                'regex:/^[\p{L}\p{N}\s\-\.\,\(\)\_]+$/u'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'active' => [
                'boolean'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da categoria é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'name.unique' => 'Esta categoria já existe.',
            'name.regex' => 'O nome contém caracteres inválidos.',
            
            'description.string' => 'A descrição deve ser um texto válido.',
            'description.max' => 'A descrição não pode ter mais de 1000 caracteres.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'active' => $this->boolean('active', true),
        ]);
    }
}
