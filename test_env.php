<?php
// Simple test to check environment loading
require_once __DIR__.'/vendor/autoload.php';

try {
    // Create Laravel application
    $app = new Illuminate\Foundation\Application(
        dirname(__DIR__)
    );

    // Bind the necessary service providers
    $app->singleton(
        Illuminate\Contracts\Console\Kernel::class,
        App\Console\Kernel::class
    );

    $app->singleton(
        Illuminate\Contracts\Debug\ExceptionHandler::class,
        App\Exceptions\Handler::class
    );

    // Load the environment
    $app->loadEnvironmentFrom('.env');
    $app->instance('app', $app);

    // Bootstrap with necessary providers
    $app->bootstrapWith([
        Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        Illuminate\Foundation\Bootstrap\BootProviders::class,
    ]);

    echo "DB_CONNECTION from config: " . $app['config']['database.default'] . "\n";
    echo "DB_CONNECTION from env: " . env('DB_CONNECTION') . "\n";
    echo "Environment: " . $app->environment() . "\n";
    echo "APP_ENV from config: " . $app['config']['app.env'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}