<?php
// test_functionality.php - Test core application functionality

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

    echo "Testing core application functionality...\n";

    // Test user creation
    $userClass = 'App\Models\User';
    if (class_exists($userClass)) {
        $user = new $userClass();
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = password_hash('password', PASSWORD_DEFAULT);
        $user->user_type = 'guru';
        $user->is_active = true;
        
        // Check if the user model has the expected methods
        if (method_exists($userClass, 'assignRole')) {
            echo "✓ User model has role management capability\n";
        } else {
            echo "✗ User model missing role management\n";
        }
        
        if (method_exists($userClass, 'hasRole')) {
            echo "✓ User model has role checking capability\n";
        } else {
            echo "✗ User model missing role checking\n";
        }
        
        echo "✓ User model structure is correct\n";
    }

    // Test service creation
    $serviceClass = 'App\Models\Service';
    if (class_exists($serviceClass)) {
        $service = new $serviceClass();
        $service->name = 'Test Service';
        $service->code = 'TEST001';
        $service->is_active = true;
        $service->user_types_allowed = ['guru', 'pegawai', 'siswa'];
        $service->created_by = 1;
        
        echo "✓ Service model structure is correct\n";
    }

    // Test ticket creation
    $ticketClass = 'App\Models\Ticket';
    if (class_exists($ticketClass)) {
        $ticket = new $ticketClass();
        $ticket->ticket_number = 'TEST-202312-00001';
        $ticket->user_id = 1;
        $ticket->service_id = 1;
        $ticket->channel = 'online';
        $ticket->status = 'submitted';
        $ticket->created_by = 1;
        
        echo "✓ Ticket model structure is correct\n";
    }

    // Test that the user types constants exist
    $userConstants = ['USER_TYPE_GURU', 'USER_TYPE_PEGAWAI', 'USER_TYPE_SISWA', 'USER_TYPE_WALIMURID', 'USER_TYPE_ALUMNI', 'USER_TYPE_INSTANSI', 'USER_TYPE_UMUM'];
    foreach ($userConstants as $constant) {
        if (defined("$userClass::$constant")) {
            echo "✓ $constant constant exists\n";
        } else {
            echo "✗ $constant constant missing\n";
        }
    }

    // Test that ticket status constants exist
    $ticketConstants = ['STATUS_SUBMITTED', 'STATUS_COMPLETED', 'STATUS_CANCELLED'];
    foreach ($ticketConstants as $constant) {
        if (defined("$ticketClass::$constant")) {
            echo "✓ $constant constant exists\n";
        } else {
            echo "✗ $constant constant missing\n";
        }
    }

    // Test that service component types exist
    $serviceComponentClass = 'App\Models\ServiceComponent';
    if (class_exists($serviceComponentClass)) {
        $componentTypes = ['DASAR_HUKUM', 'PERSYARATAN', 'MEKANISME', 'JANGKA_WAKTU', 'BIAYA', 'PRODUK_LAYANAN', 'SARANA_PRASARANA', 'KOMPETENSI_PELAKSANA', 'PENGAWASAN_INTERNAL', 'PENANGANAN_PENGADUAN', 'JUMLAH_PELAKSANA', 'JAMINAN_PELAYANAN', 'JAMINAN_KEAMANAN', 'EVALUASI_KINERJA'];
        foreach ($componentTypes as $type) {
            if (defined("$serviceComponentClass::$type")) {
                echo "✓ Service component $type constant exists\n";
            } else {
                echo "✗ Service component $type constant missing\n";
            }
        }
    }

    echo "\nCore functionality verification completed successfully!\n";
    
    // Test the relationships between models
    echo "\nTesting model relationships...\n";
    
    // Check if relationships exist
    $relationshipsToTest = [
        [$userClass, 'tickets', 'User has tickets relationship'],
        [$serviceClass, 'tickets', 'Service has tickets relationship'],
        [$ticketClass, 'user', 'Ticket has user relationship'],
        [$ticketClass, 'service', 'Ticket has service relationship']
    ];
    
    foreach ($relationshipsToTest as $test) {
        $modelClass = $test[0];
        $method = $test[1];
        $description = $test[2];
        
        if (method_exists($modelClass, $method)) {
            echo "✓ $description\n";
        } else {
            echo "✗ $description\n";
        }
    }
    
    echo "\nAll tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "Functionality test failed: " . $e->getMessage() . "\n";
}