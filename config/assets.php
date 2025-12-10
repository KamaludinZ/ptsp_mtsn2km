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
    'mode' => env('ASSET_MODE', 'compiled'), // Use compiled for production

    /*
    |--------------------------------------------------------------------------
    | Vite Dev Server Check
    |--------------------------------------------------------------------------
    |
    | Whether to check if Vite dev server is running before using Vite.
    | If false, will always attempt to use Vite in local environment.
    |
    */
    'check_vite_server' => env('CHECK_VITE_SERVER', true),

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
            'resources/css/app.css' => env('APP_CSS_FILE', 'build/assets/app-Dz8XPzYW.css'),
            'resources/css/bootstrap-custom.css' => env('BOOTSTRAP_CSS_FILE', 'build/assets/bootstrap-custom-DdipK2fU.css'),
            'resources/css/fontawesome.css' => env('FONTAWESOME_CSS_FILE', 'build/assets/fontawesome-BDveE04R.css'),
            'resources/css/accessibility.css' => env('ACCESSIBILITY_CSS_FILE', 'build/assets/accessibility-CwDc9wdA.css'),
            'resources/css/dark-mode.css' => env('DARK_MODE_CSS_FILE', 'build/assets/dark-mode-4gPRcLq_.css'),
            'resources/css/home.css' => env('HOME_CSS_FILE', 'build/assets/home-D3jisDAa.css'),
            'resources/css/pengumuman.css' => env('PENGUMUMAN_CSS_FILE', 'build/assets/pengumuman-39ntxN2n.css'),
            'resources/css/visitor-book.css' => env('VISITOR_BOOK_CSS_FILE', 'build/assets/visitor-book-C5s1ZgdJ.css'),
            'resources/css/about.css' => env('ABOUT_CSS_FILE', 'build/assets/about-BDmO3v7u.css'),
            'resources/css/contact.css' => env('CONTACT_CSS_FILE', 'build/assets/contact-tn0RQdqM.css'),
            'resources/css/admin.css' => env('ADMIN_CSS_FILE', 'build/assets/admin-DpSNkUE3.css'),
            'resources/css/loading.css' => env('LOADING_CSS_FILE', 'build/assets/loading-COPmygKa.css'),
            'resources/css/loading-screen.css' => env('LOADING_SCREEN_CSS_FILE', 'build/assets/loading-screen-BZ8i9FOJ.css'),
            'resources/css/public-layout.css' => env('PUBLIC_LAYOUT_CSS_FILE', 'build/assets/public-layout-Dfc2PAdV.css'),
            'resources/css/filament/admin/theme.css' => env('FILAMENT_THEME_CSS_FILE', 'build/assets/theme-CcY-tmRO.css'),
        ],
        'js' => [
            'resources/js/app.js' => env('APP_JS_FILE', 'js/app-compiled.js'), // Use compiled version instead of Vite-built
            'resources/js/bootstrap-bundle.js' => env('BOOTSTRAP_JS_FILE', 'js/bootstrap-bundle-compiled.js'), // Use compiled version
            'resources/js/chart-bundle.js' => env('CHART_JS_FILE', 'js/chart-bundle-compiled.js'), // Use compiled version
            'resources/js/accessibility.js' => env('ACCESSIBILITY_JS_FILE', 'js/accessibility-compiled.js'), // Use compiled version
            'resources/js/vendor.js' => env('VENDOR_JS_FILE', 'js/vendor-compiled.js'), // Use compiled version
            'resources/js/bootstrap.js' => env('BOOTSTRAP_JS_FILE', 'js/bootstrap-bundle-compiled.js'), // Use compiled version instead of build
            'resources/js/charts.js' => env('CHARTS_JS_FILE', 'js/chart-bundle-compiled.js'), // Use compiled version instead of build
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Asset Configuration
    |--------------------------------------------------------------------------
    |
    | Settings that help determine when to use Vite in development vs compiled
    | assets, especially useful when running with the dev server.
    |
    */
    'dev_options' => [
        'auto_detect_vite' => true,
        'vite_port' => 5173,
        'vite_host' => '127.0.0.1',
    ]
];