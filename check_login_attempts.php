<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LoginAttempt;

echo "=== RECENT LOGIN ATTEMPTS ===\n\n";

$attempts = LoginAttempt::latest('attempted_at')->limit(10)->get();

if ($attempts->isEmpty()) {
    echo "No login attempts recorded yet.\n";
} else {
    foreach ($attempts as $attempt) {
        $status = $attempt->successful ? '✓ SUCCESS' : '✗ FAILED';
        echo "{$status}\n";
        echo "  Email: {$attempt->email}\n";
        echo "  Reason: " . ($attempt->failure_reason ?: 'N/A') . "\n";
        echo "  IP: {$attempt->ip_address}\n";
        echo "  Time: {$attempt->attempted_at}\n";
        echo "\n";
    }
}

// Count recent failures
$email = 'admin@ptsp.mtsn2malang.sch.id';
$recentFailures = LoginAttempt::getRecentFailedAttempts($email, 60);
echo "Recent failures for {$email}: {$recentFailures}\n";
