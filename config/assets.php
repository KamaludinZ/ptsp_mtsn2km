<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Asset Loading Mode
    |--------------------------------------------------------------------------
    |
    | This option controls how assets are loaded in the application.
    | When set to 'vite', the application will use Vite for development.
    | When set to 'compiled', the application will use compiled assets.
    |
    | Supported: "vite", "compiled"
    |
    */
    'mode' => env('ASSET_MODE', 'vite'),

    /*
    |--------------------------------------------------------------------------
    | Vite Dev Server Check
    |--------------------------------------------------------------------------
    |
    | Whether to check if Vite dev server is running before using Vite.
    | If false, will always attempt to use Vite in local environment.
    |
    */
    'check_vite_server' => true,

    /*
    |--------------------------------------------------------------------------
    | Production Asset Fallback
    |--------------------------------------------------------------------------
    |
    | Fallback assets to use when in compiled mode or when Vite is not available.
    | These should match your built asset filenames in public/build/assets/
    |
    */
    'production_assets' => [
        'css' => [
            'resources/css/app.css' => env('APP_CSS_FILE', 'build/assets/app-DfRos30h.css'),
            'resources/css/bootstrap-custom.css' => env('BOOTSTRAP_CSS_FILE', 'build/assets/bootstrap-custom-Bi4iqWMz.css'),
            'resources/css/accessibility.css' => env('ACCESSIBILITY_CSS_FILE', 'build/assets/accessibility-Cf8G-GMu.css'),
            'resources/css/dark-mode.css' => env('DARK_MODE_CSS_FILE', 'build/assets/all-B7vS8Mbm.css'),
        ],
        'js' => [
            'resources/js/app.js' => env('APP_JS_FILE', 'build/assets/app-C_oYXcqv.js'),
            'resources/js/bootstrap-bundle.js' => env('BOOTSTRAP_JS_FILE', 'build/assets/bootstrap-bundle-DyPRAKW-.js'),
            'resources/js/accessibility.js' => env('ACCESSIBILITY_JS_FILE', 'build/assets/accessibility-DT1WHB_J.js'),
        ]
    ]
];