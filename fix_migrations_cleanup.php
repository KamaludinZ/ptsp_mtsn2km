<?php

/**
 * Script untuk membersihkan migrasi yang tidak sinkron
 * Menghapus record migrasi yang file-nya sudah tidak ada
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MIGRATION CLEANUP SCRIPT ===\n\n";

// Get migrations from database
$migrationsInDb = DB::table('migrations')
    ->orderBy('batch')
    ->orderBy('migration')
    ->get();

echo "Total migrations in database: " . $migrationsInDb->count() . "\n";

// Get migration files
$migrationFiles = [];
foreach (glob('database/migrations/*.php') as $file) {
    $filename = basename($file, '.php');
    $migrationFiles[] = $filename;
}

echo "Total migration files: " . count($migrationFiles) . "\n\n";

// Find migrations to remove
$toRemove = [];
foreach ($migrationsInDb as $migration) {
    if (!in_array($migration->migration, $migrationFiles)) {
        $toRemove[] = $migration;
    }
}

if (empty($toRemove)) {
    echo "✓ No cleanup needed. All migrations are in sync.\n";
    exit(0);
}

echo "Found " . count($toRemove) . " orphaned migration records:\n\n";
foreach ($toRemove as $migration) {
    echo "  Batch {$migration->batch}: {$migration->migration}\n";
}

echo "\n";
echo "Do you want to remove these orphaned records? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'yes') {
    echo "\nAborted. No changes made.\n";
    exit(0);
}

echo "\nRemoving orphaned migration records...\n";

$removed = 0;
foreach ($toRemove as $migration) {
    DB::table('migrations')
        ->where('migration', $migration->migration)
        ->delete();
    echo "  ✓ Removed: {$migration->migration}\n";
    $removed++;
}

echo "\n";
echo "Successfully removed {$removed} orphaned migration records.\n";
echo "\n=== CLEANUP COMPLETE ===\n";
echo "\nNext steps:\n";
echo "1. Run: php artisan migrate:status\n";
echo "2. Run: php artisan migrate (if needed)\n";
