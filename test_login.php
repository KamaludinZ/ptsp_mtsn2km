<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== TESTING LOGIN CREDENTIALS ===\n\n";

// Test admin user
$email = 'admin@ptsp.mtsn2malang.sch.id';
$password = 'admin123';

$user = User::where('email', $email)->first();

if ($user) {
    echo "✓ User found!\n";
    echo "  Name: {$user->name}\n";
    echo "  Email: {$user->email}\n";
    echo "  User Type: {$user->user_type}\n";
    echo "  Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
    echo "  Email Verified: " . ($user->email_verified_at ? 'Yes' : 'No') . "\n\n";

    echo "Password Test:\n";
    if (Hash::check($password, $user->password)) {
        echo "  ✓ Password '{$password}' MATCHES!\n";
    } else {
        echo "  ✗ Password '{$password}' DOES NOT MATCH!\n";
    }
} else {
    echo "✗ User with email '{$email}' NOT FOUND!\n";
}

echo "\n=== ALL USERS ===\n";
$users = User::select('name', 'email', 'user_type')->limit(10)->get();
foreach ($users as $u) {
    echo "- {$u->name} ({$u->email}) [{$u->user_type}]\n";
}
