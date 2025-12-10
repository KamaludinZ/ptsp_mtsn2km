<?php

$migrationDir = 'C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\database\migrations';

echo "Renaming migration files to correct sequential order...\n\n";

// Store all current files for reference
$allFiles = scandir($migrationDir);
$migrationFiles = array_filter($allFiles, function($file) {
    return preg_match('/\.php$/', $file) && strpos($file, 'create_') !== false;
});

echo "Current migration files found:\n";
foreach ($migrationFiles as $file) {
    echo "  - $file\n";
}
echo "\n";

// Define the correct file naming pattern based on dependencies
$renamingMap = [
    // Existing properly named files - leave as is for now
    '2024_01_01_0000017_create_users_table.php' => '2024_01_01_001000_create_users_table.php',  // Users first
    '2024_01_01_0000018_create_visitors_table.php' => '2024_01_01_001001_create_visitors_table.php',  // Visitors early
    '2024_01_01_0000019_create_workflows_table.php' => '2024_01_01_001002_create_workflows_table.php',  // Workflows before tickets
    '2024_01_01_0000020_create_workflow_steps_table.php' => '2024_01_01_001003_create_workflow_steps_table.php', // Workflow steps after workflows
    '2024_01_01_000007_create_surveys_table.php' => '2024_01_01_001004_create_surveys_table.php',  // Surveys before responses
    '2024_01_01_000003_create_service_categories_table.php' => '2024_01_01_001005_create_service_categories_table.php',  // Service categories
    '2024_01_01_000004_create_service_category_service_table.php' => '2024_01_01_001006_create_service_category_service_table.php',  // Junction table
    '2024_01_01_000001_create_complaints_table.php' => '2024_01_01_001007_create_complaints_table.php',  // Complaints after base tables
    
    // Now the renamed files from our previous fixes
    '2024_01_01_001000_create_services_table.php' => '2024_01_01_001008_create_services_table.php',  // Services after users, before others
    '2024_01_01_001003_create_tickets_table.php' => '2024_01_01_001009_create_tickets_table.php',  // Tickets after services
    '2024_01_01_001004_create_ticket_workflows_table.php' => '2024_01_01_001010_create_ticket_workflows_table.php',  // After tickets
    '2024_01_01_001005_create_ticket_workflow_steps_table.php' => '2024_01_01_001011_create_ticket_workflow_steps_table.php',  // After ticket workflows
    '2024_01_01_001001_create_service_components_table.php' => '2024_01_01_001012_create_service_components_table.php',  // After services
    '2024_01_01_001002_create_service_requirements_table.php' => '2024_01_01_001013_create_service_requirements_table.php',  // After services
    '2024_01_01_001006_create_ticket_files_table.php' => '2024_01_01_001014_create_ticket_files_table.php',  // After tickets
    '2024_01_01_001007_create_ticket_logs_table.php' => '2024_01_01_001015_create_ticket_logs_table.php',  // After tickets
    '2024_01_01_001008_create_ticket_outputs_table.php' => '2024_01_01_001016_create_ticket_outputs_table.php',  // After tickets
    '2024_01_01_001009_create_survey_responses_table.php' => '2024_01_01_001017_create_survey_responses_table.php',  // After tickets
    '2024_01_01_001010_create_survey_answers_table.php' => '2024_01_01_001018_create_survey_answers_table.php',  // After survey responses
];

$successfulRenames = 0;
foreach ($renamingMap as $oldName => $newName) {
    $oldPath = $migrationDir . DIRECTORY_SEPARATOR . $oldName;
    $newPath = $migrationDir . DIRECTORY_SEPARATOR . $newName;
    
    if (file_exists($oldPath)) {
        if (rename($oldPath, $newPath)) {
            echo "✓ Renamed: $oldName -> $newName\n";
            $successfulRenames++;
        } else {
            echo "✗ FAILED: $oldName -> $newName\n";
        }
    } else {
        echo "? Missing: $oldName (skipping)\n";
    }
}

echo "\nRenaming completed: $successfulRenames files renamed.\n";
echo "All migration files are now in proper sequential order based on dependencies.\n";