<?php

/**
 * Script untuk mengkonversi kolom JSON ke JSONB di PostgreSQL
 * JSONB lebih efisien dan mendukung GIN indexing
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== JSON TO JSONB CONVERSION SCRIPT ===\n\n";

if (DB::getDriverName() !== 'pgsql') {
    echo "❌ This script is for PostgreSQL databases only.\n";
    echo "Current driver: " . DB::getDriverName() . "\n";
    exit(1);
}

// Get all JSON columns
$jsonColumns = DB::select("
    SELECT
        table_name,
        column_name,
        is_nullable
    FROM information_schema.columns
    WHERE table_schema = 'public'
    AND data_type = 'json'
    ORDER BY table_name, column_name
");

if (empty($jsonColumns)) {
    echo "✓ No JSON columns found. All columns are already JSONB or not JSON.\n";
    exit(0);
}

echo "Found " . count($jsonColumns) . " JSON columns that can be converted to JSONB:\n\n";
foreach ($jsonColumns as $column) {
    echo "  - {$column->table_name}.{$column->column_name}\n";
}

echo "\n⚠️  WARNING: This will convert JSON columns to JSONB.\n";
echo "This operation is generally safe but:\n";
echo "  1. Backup your database first!\n";
echo "  2. This may take time on large tables\n";
echo "  3. Existing data will be preserved\n";
echo "\n";
echo "Do you want to proceed? (yes/no): ";

$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'yes') {
    echo "\n❌ Aborted. No changes made.\n";
    exit(0);
}

echo "\nStarting conversion...\n\n";

$converted = 0;
$failed = 0;

foreach ($jsonColumns as $column) {
    $tableName = $column->table_name;
    $columnName = $column->column_name;

    echo "Converting {$tableName}.{$columnName}... ";

    try {
        // Convert JSON to JSONB
        DB::statement("
            ALTER TABLE {$tableName}
            ALTER COLUMN {$columnName}
            TYPE jsonb
            USING {$columnName}::jsonb
        ");

        echo "✓ Success\n";
        $converted++;
    } catch (\Exception $e) {
        echo "✗ Failed: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n";
echo "=== CONVERSION COMPLETE ===\n";
echo "Successfully converted: {$converted}\n";
echo "Failed: {$failed}\n";

if ($converted > 0) {
    echo "\nNext steps:\n";
    echo "1. Verify data integrity: Check your application\n";
    echo "2. Run migrations again: php artisan migrate\n";
    echo "3. Update your models if needed (Laravel handles JSON/JSONB the same way)\n";
}

echo "\n=== END ===\n";
