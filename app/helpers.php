<?php

use App\Helpers\ValidationHelper;

if (!function_exists('safe_ucfirst')) {
    /**
     * Safe ucfirst function that handles null values
     * 
     * @param string|null $string
     * @param string $default
     * @return string
     */
    function safe_ucfirst(?string $string, string $default = ''): string
    {
        return ValidationHelper::safeUcfirst($string, $default);
    }
}

if (!function_exists('format_status')) {
    /**
     * Format status for display
     * 
     * @param string|null $status
     * @return string
     */
    function format_status(?string $status): string
    {
        return ValidationHelper::formatStatus($status);
    }
}

if (!function_exists('sanitize_search')) {
    /**
     * Sanitize search query
     * 
     * @param string|null $search
     * @return string
     */
    function sanitize_search(?string $search): string
    {
        return ValidationHelper::sanitizeSearchQuery($search);
    }
}

if (!function_exists('validate_email_safe')) {
    /**
     * Validate email safely
     * 
     * @param string|null $email
     * @return array
     */
    function validate_email_safe(?string $email): array
    {
        return ValidationHelper::validateEmail($email);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format currency safely handling null values
     * WHY: Prevents PHP 8.1+ deprecation warnings when null is passed to number_format()
     * 
     * @param float|int|string|null $amount
     * @param int $decimals
     * @param string $decimalSeparator
     * @param string $thousandsSeparator
     * @return string
     */
    function format_currency($amount, int $decimals = 2, string $decimalSeparator = ',', string $thousandsSeparator = '.'): string
    {
        if ($amount === null || $amount === '') {
            return number_format(0, $decimals, $decimalSeparator, $thousandsSeparator);
        }
        
        $numericAmount = is_numeric($amount) ? (float)$amount : 0;
        
        return number_format($numericAmount, $decimals, $decimalSeparator, $thousandsSeparator);
    }
}

if (!function_exists('format_price')) {
    /**
     * Format price with R$ prefix safely
     * 
     * @param float|int|string|null $price
     * @return string
     */
    function format_price($price): string
    {
        return 'R$ ' . format_currency($price, 2, ',', '.');
    }
}

if (!function_exists('safe_limit')) {
    /**
     * Safe string limit function that handles null values
     * WHY: Prevents PHP 8.1+ deprecation warnings when null is passed to Str::limit()
     * 
     * @param string|null $value
     * @param int $limit
     * @param string $end
     * @return string
     */
    function safe_limit(?string $value, int $limit = 100, string $end = '...'): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        
        return \Illuminate\Support\Str::limit($value, $limit, $end);
    }
}
