<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Input Validation and Sanitization Middleware
 * 
 * SECURITY PRINCIPLE: Never trust user input - validate and sanitize everything
 * WHY: Prevents XSS, SQL injection, and other input-based attacks
 */
class ValidateAndSanitizeInput
{
    /**
     * Dangerous patterns to remove from user input
     * WHY: Prevents code injection and XSS attacks
     */
    private const DANGEROUS_PATTERNS = [
        // Script tags and JavaScript
        '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
        '/javascript:/i',
        '/on\w+\s*=/i',
        
        // HTML tags (basic sanitization)
        '/<[^>]*>/',
        
        // SQL injection attempts
        '/(\bUNION\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|\bDROP\b|\bCREATE\b|\bALTER\b)/i',
        
        // Command injection
        '/[;&|`$<>]/',
        
        // Null bytes and control characters
        '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/',
    ];
    
    /**
     * Input validation rules by field type
     * WHY: Ensures data meets business requirements
     */
    private const VALIDATION_RULES = [
        'search' => [
            'max_length' => 100,
            'pattern' => '/^[a-zA-Z0-9\s\-_.()àáâãäéêëíîïóôõöúûüç]*$/',
            'allow_empty' => true
        ],
        'email' => [
            'max_length' => 150,
            'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'allow_empty' => false
        ],
        'name' => [
            'max_length' => 100,
            'pattern' => '/^[a-zA-Z\s\-àáâãäéêëíîïóôõöúûüç]*$/',
            'allow_empty' => false
        ],
        'text' => [
            'max_length' => 500,
            'pattern' => '/^[a-zA-Z0-9\s\-_.àáâãäéêëíîïóôõöúûüç]*$/',
            'allow_empty' => true
        ]
    ];
    
    /**
     * Handle an incoming request
     * WHY: Sanitizes and validates all user input before processing
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip validation for certain routes (API uploads, etc.)
        if ($this->shouldSkipValidation($request)) {
            return $next($request);
        }
        
        // Sanitize all input data
        $sanitizedData = $this->sanitizeInputData($request->all());
        
        // Replace request data with sanitized version
        $request->merge($sanitizedData);
        
        // Validate critical inputs
        $this->validateCriticalInputs($request);
        
        return $next($request);
    }
    
    /**
     * Check if validation should be skipped for specific routes
     * WHY: Some routes need different validation (file uploads, API endpoints)
     */
    private function shouldSkipValidation(Request $request): bool
    {
        $skipRoutes = [
            'api/upload',
            'admin/file-upload',
            'webhook'
        ];
        
        $currentPath = $request->path();
        
        foreach ($skipRoutes as $skipRoute) {
            if (Str::startsWith($currentPath, $skipRoute)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sanitize all input data recursively
     * WHY: Removes dangerous content from all user inputs
     */
    private function sanitizeInputData(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInputData($value);
            } else {
                $sanitized[$key] = $this->sanitizeString($value);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize individual string value
     * WHY: Removes dangerous patterns and normalizes input
     */
    private function sanitizeString($value): string
    {
        if (!is_string($value)) {
            return '';
        }
        
        // Remove dangerous patterns
        foreach (self::DANGEROUS_PATTERNS as $pattern) {
            $value = preg_replace($pattern, '', $value);
        }
        
        // Normalize whitespace
        $value = preg_replace('/\s+/', ' ', $value);
        
        // Trim and limit length for basic safety
        $value = trim($value);
        $value = Str::limit($value, 1000, '');
        
        return $value;
    }
    
    /**
     * Validate critical inputs that have specific requirements
     * WHY: Ensures data meets business logic requirements
     */
    private function validateCriticalInputs(Request $request): void
    {
        // Validate search input
        if ($request->has('search')) {
            $this->validateInput($request->input('search'), 'search', 'Busca');
        }
        
        // Validate email inputs
        $emailFields = ['email', 'newsletter_email', 'contact_email'];
        foreach ($emailFields as $field) {
            if ($request->has($field)) {
                $this->validateInput($request->input($field), 'email', 'E-mail');
            }
        }
        
        // Validate name inputs
        $nameFields = ['name', 'first_name', 'last_name'];
        foreach ($nameFields as $field) {
            if ($request->has($field)) {
                $this->validateInput($request->input($field), 'name', 'Nome');
            }
        }
    }
    
    /**
     * Validate single input against specific rules
     * WHY: Provides detailed validation with proper error messages
     */
    private function validateInput(string $value, string $type, string $fieldName): void
    {
        $rule = self::VALIDATION_RULES[$type] ?? self::VALIDATION_RULES['text'];
        
        // Check if empty value is allowed
        if (empty($value) && !$rule['allow_empty']) {
            abort(422, "{$fieldName} é obrigatório");
        }
        
        // Skip further validation if empty and allowed
        if (empty($value) && $rule['allow_empty']) {
            return;
        }
        
        // Check maximum length
        if (strlen($value) > $rule['max_length']) {
            abort(422, "{$fieldName} deve ter no máximo {$rule['max_length']} caracteres");
        }
        
        // Check pattern
        if (!preg_match($rule['pattern'], $value)) {
            abort(422, "{$fieldName} contém caracteres inválidos");
        }
    }
    
    /**
     * Log suspicious input attempts
     * WHY: Security monitoring and attack detection
     */
    private function logSuspiciousInput(Request $request, string $reason): void
    {
        Log::warning('Suspicious input detected', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'reason' => $reason,
            'input_data' => $request->except(['password', 'password_confirmation'])
        ]);
    }
}
