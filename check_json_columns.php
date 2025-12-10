<?php

/**
 * Script untuk memeriksa kolom JSON dan memberikan rekomendasi
 * PostgreSQL: JSON vs JSONB
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== CHECKING JSON COLUMNS IN POSTGRESQL ===\n\n";

if (DB::getDriverName() !== 'pgsql') {
    echo "This script is for PostgreSQL databases only.\n";
    echo "Current driver: " . DB::getDriverName() . "\n";
    exit(1);
}

// Get all JSON/JSONB columns
$result = DB::select("
    SELECT
        table_name,
        column_name,
        data_type,
        is_nullable
    FROM information_schema.columns
    WHERE table_schema = 'public'
    AND data_type IN ('json', 'jsonb')
    ORDER BY table_name, column_name
");

if (empty($result)) {
    echo "No JSON/JSONB columns found in database.\n";
    exit(0);
}

echo "Found " . count($result) . " JSON/JSONB columns:\n\n";

$jsonColumns = [];
$jsonbColumns = [];

foreach ($result as $column) {
    if ($column->data_type === 'json') {
        $jsonColumns[] = $column;
        echo "⚠️  {$column->table_name}.{$column->column_name} - JSON (should be JSONB for better performance)\n";
    } else {
        $jsonbColumns[] = $column;
        echo "✓  {$column->table_name}.{$column->column_name} - JSONB\n";
    }
}

echo "\n";
echo "=== SUMMARY ===\n";
echo "JSONB columns (good): " . count($jsonbColumns) . "\n";
echo "JSON columns (needs conversion): " . count($jsonColumns) . "\n";

if (!empty($jsonColumns)) {
    echo "\n=== RECOMMENDATIONS ===\n";
    echo "JSON columns should be converted to JSONB for better:\n";
    echo "  - Performance (indexing support)\n";
    echo "  - GIN index support\n";
    echo "  - Query optimization\n";
    echo "\n";
    echo "Columns that need conversion:\n";
    foreach ($jsonColumns as $column) {
        echo "  - {$column->table_name}.{$column->column_name}\n";
    }
    echo "\n";
    echo "To convert, create a migration with:\n";
    echo "DB::statement(\"ALTER TABLE table_name ALTER COLUMN column_name TYPE jsonb USING column_name::jsonb\");\n";
    echo "\n";
    echo "Or run the conversion script:\n";
    echo "php convert_json_to_jsonb.php\n";
}

echo "\n=== END ===\n";
