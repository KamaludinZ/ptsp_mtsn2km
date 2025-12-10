<?php

$migrationDir = 'C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\database\migrations';

echo "Adding missing survey-related migration files to proper sequence...\n\n";

// Mapping to add the missing survey-related files in proper order
$renamingMap = [
    // Original files we already renamed stay the same
    // New survey-related files that need to be moved to proper sequence
    '2025_11_05_053912_create_survey_questions_table.php' => '2024_01_01_000021_create_survey_questions_table.php',  // After survey_answers, references surveys
    '2025_11_06_010238_create_survey_editions_table.php' => '2024_01_01_000022_create_survey_editions_table.php',  // Survey editions
    '2025_11_06_010456_create_survey_unsur_table.php' => '2024_01_01_000023_create_survey_unsur_table.php',  // Survey unsur
    '2025_11_06_023738_create_survey_archives_table.php' => '2024_01_01_000024_create_survey_archives_table.php',  // Survey archives
];

echo "Renaming survey-related files to proper sequence...\n";

$successfulRenames = 0;
$errorCount = 0;

foreach ($renamingMap as $oldName => $newName) {
    $oldPath = $migrationDir . DIRECTORY_SEPARATOR . $oldName;
    $newPath = $migrationDir . DIRECTORY_SEPARATOR . $newName;
    
    if (file_exists($oldPath)) {
        if (rename($oldPath, $newPath)) {
            echo "✓ Renamed: $oldName -> $newName\n";
            $successfulRenames++;
        } else {
            echo "✗ FAILED: Could not rename $oldName to $newName\n";
            $errorCount++;
        }
    } else {
        echo "? SKIP: File $oldName not found\n";
    }
}

echo "\nRenaming completed: $successfulRenames files renamed, $errorCount errors.\n";

// Show current state
echo "\nCurrent survey-related files:\n";
$surveyFiles = array_filter(scandir($migrationDir), function($file) {
    return preg_match('/\.php$/', $file) && strpos($file, 'survey') !== false;
});

sort($surveyFiles);
foreach ($surveyFiles as $file) {
    echo "  - $file\n";
}