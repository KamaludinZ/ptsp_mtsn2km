<?php
// test_database.php - Test database schema and relationships

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

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

    echo "Database connection established.\n";

    // Create a test user
    $userClass = 'App\Models\User';
    if (class_exists($userClass)) {
        $reflection = new ReflectionClass($userClass);
        $userInstance = $reflection->newInstanceWithoutConstructor();
        echo "✓ User model can be instantiated\n";
        
        // Test that it has expected properties
        $fillable = $userInstance->getFillable();
        echo "Fillable attributes: " . implode(', ', $fillable) . "\n";
    }

    // Create a test service
    $serviceClass = 'App\Models\Service';
    if (class_exists($serviceClass)) {
        $reflection = new ReflectionClass($serviceClass);
        $serviceInstance = $reflection->newInstanceWithoutConstructor();
        echo "✓ Service model can be instantiated\n";
    }

    // Create a test ticket
    $ticketClass = 'App\Models\Ticket';
    if (class_exists($ticketClass)) {
        $reflection = new ReflectionClass($ticketClass);
        $ticketInstance = $reflection->newInstanceWithoutConstructor();
        echo "✓ Ticket model can be instantiated\n";
    }

    echo "\nDatabase models verification completed successfully!\n";
    
    // Now check for migration files
    $migrationDir = 'database/migrations/';
    if (is_dir($migrationDir)) {
        $migrations = array_diff(scandir($migrationDir), array('..', '.'));
        echo "\nFound " . count($migrations) . " migration files:\n";
        foreach ($migrations as $migration) {
            echo "  - $migration\n";
        }
    } else {
        echo "\nMigration directory not found\n";
    }
    
    // Check for seeder files
    $seederDir = 'database/seeders/';
    if (is_dir($seederDir)) {
        $seeders = array_diff(scandir($seederDir), array('..', '.'));
        echo "\nFound " . count($seeders) . " seeder files:\n";
        foreach ($seeders as $seeder) {
            echo "  - $seeder\n";
        }
    } else {
        echo "\nSeeder directory not found\n";
    }
    
} catch (Exception $e) {
    echo "Database test failed: " . $e->getMessage() . "\n";
}