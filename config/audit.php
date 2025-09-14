<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Audit System Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which actions should be logged and the thresholds for
    | performance monitoring.
    |
    */

    // Enable/disable audit system
    'enabled' => env('AUDIT_ENABLED', false),

    // Log user actions (view, add to cart, etc.)
    'log_view_actions' => env('AUDIT_LOG_VIEW_ACTIONS', false),
    'log_cart_actions' => env('AUDIT_LOG_CART_ACTIONS', true),
    'log_product_actions' => env('AUDIT_LOG_PRODUCT_ACTIONS', true),
    'log_admin_actions' => env('AUDIT_LOG_ADMIN_ACTIONS', true),
    'log_api_requests' => env('AUDIT_LOG_API_REQUESTS', true),

    // Performance thresholds (in milliseconds)
    'slow_request_threshold' => env('AUDIT_SLOW_REQUEST_THRESHOLD', 500),
    'slow_query_threshold' => env('AUDIT_SLOW_QUERY_THRESHOLD', 100),

    // Data retention (in days)
    'retention_days' => [
        'audit' => env('AUDIT_RETENTION_AUDIT', 90),
        'security' => env('AUDIT_RETENTION_SECURITY', 180),
        'performance' => env('AUDIT_RETENTION_PERFORMANCE', 7),
        'api' => env('AUDIT_RETENTION_API', 30),
        'database' => env('AUDIT_RETENTION_DATABASE', 7),
    ],

    // Sensitive fields to redact in logs
    'sensitive_fields' => [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'api_key',
        'credit_card',
        'cvv',
        'authorization',
        'secret',
        'private_key',
    ],

    // Maximum log size before rotation (in MB)
    'max_log_size' => env('AUDIT_MAX_LOG_SIZE', 50),
];
