<?php

/**
 * Script untuk memverifikasi struktur file migrasi
 * Memeriksa apakah semua migrasi memiliki method up() dan down()
 */

echo "=== MIGRATION STRUCTURE VERIFICATION ===\n\n";

$migrationFiles = glob('database/migrations/*.php');
$issues = [];
$checked = 0;

foreach ($migrationFiles as $file) {
    $checked++;
    $filename = basename($file);
    $content = file_get_contents($file);

    $hasUp = preg_match('/public\s+function\s+up\s*\(/', $content);
    $hasDown = preg_match('/public\s+function\s+down\s*\(/', $content);
    $hasSchemaCheck = preg_match('/(Schema::hasTable|Schema::hasColumn|if\s*\()/', $content);

    $fileIssues = [];

    if (!$hasUp) {
        $fileIssues[] = 'Missing up() method';
    }

    if (!$hasDown) {
        $fileIssues[] = 'Missing down() method';
    }

    // Check if it's a create table migration
    if (preg_match('/create.*table/i', $filename)) {
        if (!preg_match('/Schema::hasTable|!Schema::hasTable/', $content)) {
            $fileIssues[] = 'CREATE TABLE migration should check if table exists';
        }
    }

    // Check if it's an add column migration
    if (preg_match('/add.*to.*table/i', $filename)) {
        if (!preg_match('/Schema::hasColumn|!Schema::hasColumn/', $content)) {
            $fileIssues[] = 'ADD COLUMN migration should check if column exists';
        }
    }

    if (!empty($fileIssues)) {
        $issues[$filename] = $fileIssues;
    }
}

echo "Checked {$checked} migration files.\n\n";

if (empty($issues)) {
    echo "✓ All migrations have proper structure!\n";
} else {
    echo "Found issues in " . count($issues) . " migration files:\n\n";

    foreach ($issues as $filename => $fileIssues) {
        echo "⚠ {$filename}\n";
        foreach ($fileIssues as $issue) {
            echo "  - {$issue}\n";
        }
        echo "\n";
    }

    echo "\nRecommendations:\n";
    echo "1. Add down() methods to rollback migrations\n";
    echo "2. Add conditional checks (hasTable/hasColumn) to prevent duplicate errors\n";
    echo "3. Review and update affected migration files\n";
}

echo "\n=== END OF VERIFICATION ===\n";
