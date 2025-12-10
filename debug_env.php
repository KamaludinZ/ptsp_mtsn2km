<?php
// Direct test without Laravel cache
require_once __DIR__.'/vendor/autoload.php';

// Directly read the .env file
$envContent = file_get_contents(__DIR__.'/.env');
echo "Raw .env content:\n";
echo $envContent . "\n\n";

// Parse manually to see values
$lines = explode("\n", $envContent);
echo "Environment variables found:\n";
foreach ($lines as $line) {
    if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, "\"'\r\n ");
        if (in_array($key, ['DB_CONNECTION', 'DB_DATABASE', 'APP_ENV'])) {
            echo $key . ' = ' . $value . "\n";
        }
    }
}

// Create a new Laravel application instance
use Illuminate\Foundation\Application;

$app = new Application(__DIR__);

// This should load environment variables automatically
echo "\nLaravel env from direct access: " . $_ENV['DB_CONNECTION'] ?? 'NOT SET' . "\n";