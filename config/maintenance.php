<?php

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;

return [

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the hosts that should be allowed to bypass the
    | maintenance mode. By default, all hosts will be allowed to bypass
    | maintenance mode, but you may restrict access to specific hosts.
    |
    */

    'allowed_hosts' => [
        // Example: '192.168.1.100', '10.0.0.0/8'
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Retry After
    |--------------------------------------------------------------------------
    |
    | Specifies the number of seconds after which the request should be retried
    | when the application is in maintenance mode. This header is used by
    | browsers and other HTTP clients to determine when to retry the request.
    |
    */

    'retry_after' => env('MAINTENANCE_RETRY_AFTER', 300),

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Message
    |--------------------------------------------------------------------------
    |
    | A custom message to display when the application is in maintenance mode.
    | This can be used to provide more information to the end users about
    | the maintenance and expected completion time.
    |
    */

    'message' => 'Sistem sedang dalam perawatan untuk meningkatkan kualitas layanan.',

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Status
    |--------------------------------------------------------------------------
    |
    | A flag to control if maintenance mode is enabled. This can be controlled
    | via environment variable MAINTENANCE_MODE in the .env file.
    |
    */

    'enabled' => env('MAINTENANCE_MODE', false),

];