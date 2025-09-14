<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

/**
 * Product Search Request Validation
 * 
 * SECURITY PRINCIPLE: Validate search inputs to prevent injection attacks
 * WHY: Ensures search queries are safe and properly formatted
 */
class ProductSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true; // Search is public
    }
    
    /**
     * Get the validation rules that apply to the request
     * WHY: Prevents malicious search queries and ensures data integrity
     */
    public function rules(): array
    {
        return [
            'search' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_.()àáâãäéêëíîïóôõöúûüç]*$/'
            ],
            'category' => [
                'nullable',
                'integer',
                'exists:categories,id'
            ],
            'min_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99'
            ],
            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
                'gte:min_price'
            ],
            'sort' => [
                'nullable',
                'string',
                'in:name_asc,name_desc,price_asc,price_desc,created_asc,created_desc'
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
                'max:10000'
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100'
            ]
        ];
    }
    
    /**
     * Get custom error messages for validator errors
     * WHY: Provides user-friendly error messages in Portuguese
     */
    public function messages(): array
    {
        return [
            'search.regex' => 'A busca deve conter apenas letras, números e caracteres básicos',
            'search.max' => 'A busca deve ter no máximo 100 caracteres',
            'category.integer' => 'Categoria deve ser um número válido',
            'category.exists' => 'Categoria selecionada não existe',
            'min_price.numeric' => 'Preço mínimo deve ser um número',
            'min_price.min' => 'Preço mínimo deve ser positivo',
            'min_price.max' => 'Preço mínimo muito alto',
            'max_price.numeric' => 'Preço máximo deve ser um número',
            'max_price.min' => 'Preço máximo deve ser positivo',
            'max_price.max' => 'Preço máximo muito alto',
            'max_price.gte' => 'Preço máximo deve ser maior ou igual ao preço mínimo',
            'sort.in' => 'Opção de ordenação inválida',
            'page.integer' => 'Número da página deve ser um número inteiro',
            'page.min' => 'Número da página deve ser pelo menos 1',
            'page.max' => 'Número da página muito alto',
            'per_page.integer' => 'Itens por página deve ser um número inteiro',
            'per_page.min' => 'Deve mostrar pelo menos 1 item por página',
            'per_page.max' => 'Máximo de 100 itens por página'
        ];
    }
    
    /**
     * Handle a failed validation attempt
     * WHY: Provides consistent error handling for search validation
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Log suspicious search attempts
        if ($this->hasInvalidChars()) {
            Log::warning('Suspicious search attempt detected', [
                'ip' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'search_query' => $this->input('search'),
                'errors' => $validator->errors()->all()
            ]);
        }
        
        parent::failedValidation($validator);
    }
    
    /**
     * Check if search contains potentially dangerous characters
     * WHY: Detects possible injection attempts
     */
    private function hasInvalidChars(): bool
    {
        $search = $this->input('search', '');
        
        $dangerousPatterns = [
            '/[<>&"\']/i',
            '/script/i',
            '/javascript/i',
            '/(union|select|insert|update|delete|drop)/i',
            '/[;&|`$]/i'
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $search)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Prepare the data for validation
     * WHY: Sanitizes input before validation
     */
    protected function prepareForValidation()
    {
        if ($this->has('search')) {
            $this->merge([
                'search' => $this->sanitizeSearch($this->input('search'))
            ]);
        }
    }
    
    /**
     * Sanitize search input
     * WHY: Removes dangerous content while preserving valid search terms
     */
    private function sanitizeSearch(?string $search): ?string
    {
        if (empty($search)) {
            return null;
        }
        
        // Remove HTML tags
        $search = strip_tags($search);
        
        // Remove dangerous characters
        $search = preg_replace('/[<>&"\']/', '', $search);
        
        // Normalize whitespace
        $search = trim(preg_replace('/\s+/', ' ', $search));
        
        return empty($search) ? null : $search;
    }
}
