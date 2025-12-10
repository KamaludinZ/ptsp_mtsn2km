<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

echo "=== CHECKING ROLES ===\n\n";

// Check if admin role exists
$adminRole = Role::where('name', 'admin')->first();
if ($adminRole) {
    echo "✓ admin role EXISTS\n";
} else {
    echo "✗ admin role NOT FOUND\n";
    echo "  Available roles:\n";
    $roles = Role::all();
    foreach ($roles as $role) {
        echo "  - {$role->name}\n";
    }
}

echo "\n=== CHECKING ADMIN USER ===\n\n";

$user = User::where('email', 'admin@ptsp.mtsn2malang.sch.id')->first();
if ($user) {
    echo "User: {$user->name}\n";
    echo "Roles assigned:\n";
    $userRoles = $user->getRoleNames();
    if ($userRoles->isEmpty()) {
        echo "  ✗ NO ROLES assigned!\n";
    } else {
        foreach ($userRoles as $role) {
            echo "  - {$role}\n";
        }
    }

    // Test authentication manually
    echo "\n=== MANUAL AUTH TEST ===\n";

    $credentials = [
        'email' => 'admin@ptsp.mtsn2malang.sch.id',
        'password' => 'admin123'
    ];

    if (Auth::attempt($credentials)) {
        echo "✓ Auth::attempt() SUCCESS\n";
        echo "  Logged in as: " . Auth::user()->name . "\n";
        Auth::logout();
    } else {
        echo "✗ Auth::attempt() FAILED\n";
        echo "  But Hash::check() says: " . (Hash::check('admin123', $user->password) ? 'MATCH' : 'NO MATCH') . "\n";

        // Debug
        echo "\n  Debug info:\n";
        echo "  - is_active: " . ($user->is_active ? 'true' : 'false') . "\n";
        echo "  - email_verified_at: " . ($user->email_verified_at ? $user->email_verified_at : 'NULL') . "\n";
    }
}
