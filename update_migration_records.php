<?php

require_once 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Updating migration records to match renamed files...\n";

// Mapping of old migration names to new migration names
$renamedMigrations = [
    '2024_01_01_000018_create_services_table.php' => '2024_01_01_001000_create_services_table.php',
    '2024_01_01_000021_create_service_components_table.php' => '2024_01_01_001001_create_service_components_table.php',
    '2024_01_01_000023_create_service_requirements_table.php' => '2024_01_01_001002_create_service_requirements_table.php',
    '2024_01_01_000022_create_tickets_table.php' => '2024_01_01_001003_create_tickets_table.php',
    '2024_01_01_000030_create_ticket_workflows_table.php' => '2024_01_01_001004_create_ticket_workflows_table.php',
    '2024_01_01_000031_create_ticket_workflow_steps_table.php' => '2024_01_01_001005_create_ticket_workflow_steps_table.php',
    '2024_01_01_000026_create_ticket_files_table.php' => '2024_01_01_001006_create_ticket_files_table.php',
    '2024_01_01_000027_create_ticket_logs_table.php' => '2024_01_01_001007_create_ticket_logs_table.php',
    '2024_01_01_000028_create_ticket_outputs_table.php' => '2024_01_01_001008_create_ticket_outputs_table.php',
    '2024_01_01_000024_create_survey_responses_table.php' => '2024_01_01_001009_create_survey_responses_table.php',
    '2024_01_01_000029_create_survey_answers_table.php' => '2024_01_01_001010_create_survey_answers_table.php',
];

$updated = 0;

foreach ($renamedMigrations as $oldName => $newName) {
    $result = DB::table('migrations')
        ->where('migration', $oldName)
        ->update(['migration' => $newName]);
    
    if ($result > 0) {
        echo "Updated: $oldName -> $newName\n";
        $updated++;
    } else {
        echo "Not found in DB: $oldName (may not have been run yet)\n";
    }
}

echo "\nUpdated $updated migration records in the database.\n";
echo "Migration renaming and database update complete!\n";