<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== CHECKING MIGRATION SYNCHRONIZATION ===\n\n";

// Check migrations table
$migrationsInDb = DB::table('migrations')
    ->orderBy('id')
    ->get()
    ->pluck('migration')
    ->toArray();

echo "Total migrations in database: " . count($migrationsInDb) . "\n";

// Check migration files
$migrationFiles = [];
foreach (glob('database/migrations/*.php') as $file) {
    $filename = basename($file, '.php');
    $migrationFiles[] = $filename;
}

echo "Total migration files: " . count($migrationFiles) . "\n\n";

// Check for migrations in files but not in database
$notInDb = array_diff($migrationFiles, $migrationsInDb);
if (!empty($notInDb)) {
    echo "MIGRATIONS IN FILES BUT NOT IN DATABASE:\n";
    foreach ($notInDb as $migration) {
        echo "  - $migration\n";
    }
    echo "\n";
} else {
    echo "All migration files are recorded in database.\n\n";
}

// Check for migrations in database but not in files
$notInFiles = array_diff($migrationsInDb, $migrationFiles);
if (!empty($notInFiles)) {
    echo "MIGRATIONS IN DATABASE BUT NOT IN FILES:\n";
    foreach ($notInFiles as $migration) {
        echo "  - $migration\n";
    }
    echo "\n";
}

// Check if media table exists
echo "=== CHECKING TABLES ===\n";
echo "media table exists: " . (Schema::hasTable('media') ? 'YES' : 'NO') . "\n";
echo "activity_log table exists: " . (Schema::hasTable('activity_log') ? 'YES' : 'NO') . "\n";

// Check media migration status
$mediaInDb = in_array('2025_11_13_032319_create_media_table', $migrationsInDb);
echo "\nmedia migration in database: " . ($mediaInDb ? 'YES' : 'NO') . "\n";

if ($mediaInDb && Schema::hasTable('media')) {
    echo "\n✓ Media table and migration are in sync.\n";
} elseif (!$mediaInDb && !Schema::hasTable('media')) {
    echo "\n✓ Media table migration has not run yet.\n";
} else {
    echo "\n✗ SYNC ISSUE DETECTED!\n";
    if ($mediaInDb && !Schema::hasTable('media')) {
        echo "  Migration recorded but table doesn't exist.\n";
    } else {
        echo "  Table exists but migration not recorded.\n";
    }
}

echo "\n=== END OF CHECK ===\n";
