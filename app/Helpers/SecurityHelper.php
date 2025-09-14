<?php

namespace App\Helpers;

class SecurityHelper
{
    public static function sanitizeSearchQuery($query)
    {
        if (empty($query)) {
            return '';
        }

        $query = trim($query);
        $query = preg_replace('/[^\p{L}\p{N}\s\-\.\,]/u', '', $query);
        $query = preg_replace('/\s+/', ' ', $query);
        
        return mb_substr($query, 0, 100);
    }

    public static function validateProductId($id)
    {
        return is_numeric($id) && (int)$id > 0 && (int)$id <= 2147483647;
    }

    public static function validateQuantity($quantity)
    {
        return is_numeric($quantity) && (int)$quantity > 0 && (int)$quantity <= 100;
    }

    public static function validatePrice($price)
    {
        return is_numeric($price) && (float)$price >= 0 && (float)$price <= 999999.99;
    }

    public static function sanitizeFileName($filename)
    {
        $filename = preg_replace('/[^a-zA-Z0-9\-\_\.]/', '', $filename);
        $filename = preg_replace('/\.+/', '.', $filename);
        $filename = trim($filename, '.-_');
        
        return mb_substr($filename, 0, 255);
    }

    public static function generateSecureToken($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }

    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function escapeOutput($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'escapeOutput'], $data);
        }
        
        if (is_string($data)) {
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        return $data;
    }
}
