<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Production Environment Configuration
    |--------------------------------------------------------------------------
    |
    | These settings are specifically for the production environment to ensure
    | optimal performance, security, and reliability.
    |
    */

    // Cache Configuration for Production
    'cache' => [
        'default' => env('CACHE_DRIVER', 'redis'),
        'fallback' => 'file',
        'prefix' => env('CACHE_PREFIX', 'ptsp_prod_'),
    ],

    // Queue Configuration for Production
    'queue' => [
        'default' => env('QUEUE_CONNECTION', 'redis'),
        'failed_job_attempts' => 3,
        'failed_job_retry_after' => 180, // 3 minutes
    ],

    // Session Configuration for Production
    'session' => [
        'driver' => env('SESSION_DRIVER', 'redis'),
        'lifetime' => env('SESSION_LIFETIME', 120),
        'secure' => env('SESSION_SECURE_COOKIE', true),
        'http_only' => true,
        'same_site' => 'lax',
    ],

    // Security Configuration for Production
    'security' => [
        'force_https' => true,
        'hsts_enabled' => true,
        'hsts_max_age' => 31536000, // 1 year
        'hsts_include_subdomains' => true,
        'hsts_preload' => false,
        'csp_enabled' => true,
    ],

    // Logging Configuration for Production
    'logging' => [
        'default' => env('LOG_CHANNEL', 'stack'),
        'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'errorlog'),
        'level' => env('LOG_LEVEL', 'error'),
    ],

    // Performance Configuration for Production
    'performance' => [
        'enable_query_log' => false,
        'enable_debugbar' => false,
        'opcache_enabled' => true,
        'max_execution_time' => 120,
        'memory_limit' => '512M',
    ],

    // Monitoring and Health Checks
    'monitoring' => [
        'enable_health_checks' => true,
        'health_check_timeout' => 30,
        'enable_downtime_notifications' => true,
    ],
];