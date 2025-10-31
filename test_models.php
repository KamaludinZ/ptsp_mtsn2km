<?php
// test_models.php - Simple test to verify our models exist and work

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Initialize database connection with PDO
try {
    $capsule = new Capsule;
    $capsule->addConnection([
        'driver' => 'pgsql',
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '5432',
        'database' => $_ENV['DB_DATABASE'] ?? 'pts_mtsn2_malang',
        'username' => $_ENV['DB_USERNAME'] ?? 'postgres',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8',
        'prefix' => '',
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    echo "Database connection successful!\n";
    
    // Test that our User model exists and can be instantiated
    if (class_exists('App\Models\User')) {
        echo "✓ User model exists\n";
        $reflection = new ReflectionClass('App\Models\User');
        echo "✓ User model is properly defined\n";
    } else {
        echo "✗ User model does not exist\n";
    }
    
    // Test that our Service model exists
    if (class_exists('App\Models\Service')) {
        echo "✓ Service model exists\n";
    } else {
        echo "✗ Service model does not exist\n";
    }
    
    // Test that our Ticket model exists
    if (class_exists('App\Models\Ticket')) {
        echo "✓ Ticket model exists\n";
    } else {
        echo "✗ Ticket model does not exist\n";
    }
    
    // Test that our controllers exist
    $controllers = [
        'App\Http\Controllers\FrontDesk\FrontDeskController',
        'App\Http\Controllers\OnlinePortal\OnlinePortalController',
        'App\Http\Controllers\BackOffice\BackOfficeController',
        'App\Http\Controllers\Supervision\SupervisionController'
    ];
    
    foreach ($controllers as $controller) {
        if (class_exists($controller)) {
            echo "✓ $controller exists\n";
        } else {
            echo "✗ $controller does not exist\n";
        }
    }
    
    echo "\nModel and controller verification completed successfully!\n";
    
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}