<?php

require_once 'vendor/autoload.php';

echo "Testing Laravel Bootstrapping...\n";

try {
    // Test if we can create the application
    $app = require_once 'bootstrap/app.php';
    echo "✓ Application bootstrap successful\n";
    
    // Test if we can access the kernel
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "✓ HTTP Kernel resolved successfully\n";
    
    echo "Laravel is properly configured and ready to serve requests!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}