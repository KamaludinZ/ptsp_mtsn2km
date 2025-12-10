<?php

require_once 'vendor/autoload.php';

// Create application
$app = require_once 'bootstrap/app.php';

// Start Laravel
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;
use App\Models\User;

echo "=== Current Roles Cleanup ===\n\n";

// Define the expected roles
$expectedRoles = [
    'admin' => 'Admin',
    'kepala_sekolah' => 'Kepala Sekolah',
    'kepala_tu' => 'Kepala TU',
    'waka' => 'Waka',
    'back_office' => 'Back Office',
    'front_desk' => 'Front Desk',
    'gtk' => 'Guru dan Pegawai',
    'pemohon' => 'Pemohon (Siswa, Wali Murid, Alumni, Instansi, Umum)',
];

// Get all current roles
$currentRoles = Role::all();
echo "Current roles in system:\n";
foreach ($currentRoles as $role) {
    echo "  - {$role->name}\n";
}

echo "\nExpected roles should be:\n";
foreach ($expectedRoles as $role => $description) {
    echo "  - {$role} ({$description})\n";
}

// Check for extra roles that should be removed
$extraRoles = [];
foreach ($currentRoles as $role) {
    if (!array_key_exists($role->name, $expectedRoles)) {
        $extraRoles[] = $role->name;
    }
}

echo "\nRoles to be removed:\n";
if (count($extraRoles) > 0) {
    foreach ($extraRoles as $roleName) {
        echo "  - {$roleName}\n";
    }
} else {
    echo "  None (all roles are expected)\n";
}

// Show users with roles that might need to be mapped
echo "\n=== Users with Extra Roles ===\n";
$users = User::with('roles')->get();
foreach ($users as $user) {
    $userRoles = $user->roles->pluck('name')->toArray();
    $extraUserRoles = array_intersect($userRoles, $extraRoles);
    
    if (count($extraUserRoles) > 0) {
        echo "User: {$user->name} ({$user->email})\n";
        echo "  Current roles: " . implode(', ', $userRoles) . "\n";
        echo "  Extra roles: " . implode(', ', $extraUserRoles) . "\n";
        
        // Suggest proper role mapping
        foreach ($extraUserRoles as $extraRole) {
            if (in_array($extraRole, ['guru', 'pegawai'])) {
                echo "  -> Should be mapped to: gtk\n";
            } elseif (in_array($extraRole, ['siswa', 'walimurid', 'alumni', 'instansi', 'umum'])) {
                echo "  -> Should be mapped to: pemohon\n";
            } else {
                echo "  -> Needs role mapping decision\n";
            }
        }
        echo "\n";
    }
}