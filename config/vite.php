<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Vite Development Server
    |--------------------------------------------------------------------------
    |
    | This option controls how the Vite development server is used.
    | In production, set this to false to always use built assets.
    | In development, you can set this to true to use the development server.
    |
    */
    'use_dev_server' => env('VITE_USE_DEV_SERVER', true),

    /*
    |--------------------------------------------------------------------------
    | Vite Development Server Host
    |--------------------------------------------------------------------------
    |
    | The host address of the Vite development server. This is used when 
    | checking if the Vite server is available.
    |
    */
    'dev_server_host' => env('VITE_DEV_SERVER_HOST', 'localhost'),

    /*
    |--------------------------------------------------------------------------
    | Vite Development Server Port
    |--------------------------------------------------------------------------
    |
    | The port of the Vite development server. This is used when 
    | checking if the Vite server is available.
    |
    */
    'dev_server_port' => env('VITE_DEV_SERVER_PORT', 5173),

    /*
    |--------------------------------------------------------------------------
    | Production Asset Fallback
    |--------------------------------------------------------------------------
    |
    | Whether to fallback to built assets when Vite server is not available
    | in development environment.
    |
    */
    'fallback_to_production' => env('VITE_FALLBACK_TO_PRODUCTION', true),

    /*
    |--------------------------------------------------------------------------
    | Production Check Interval
    |--------------------------------------------------------------------------
    |
    | How often (in seconds) to check if the Vite development server is running
    | before falling back to production assets in development environment.
    | Set to 0 to disable checking in development.
    |
    */
    'production_check_interval' => env('VITE_PRODUCTION_CHECK_INTERVAL', 60),
];