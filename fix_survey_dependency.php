<?php

$migrationDir = 'C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\database\migrations';

echo "Fixing survey_answers dependency - it should come AFTER survey_questions...\n\n";

// We need to reorder based on the actual foreign key dependencies:
// survey_questions should come before survey_answers 
// surveys -> survey_questions -> survey_responses -> survey_answers

// Current order is incorrect: survey_answers (000020) is before survey_questions (000021)
// We need: survey_questions (000020), survey_answers (000021), then others

$renamingMap = [
    // Move survey_answers to come after survey_questions
    '2024_01_01_000020_create_survey_answers_table.php' => '2024_01_01_000025_create_survey_answers_table.php',  // Move to after all survey_* files
    // Also fix the sequence for a proper dependency order:
    '2024_01_01_000021_create_survey_questions_table.php' => '2024_01_01_000021_create_survey_questions_table.php', // Keep this one
    '2024_01_01_000022_create_survey_editions_table.php' => '2024_01_01_000022_create_survey_editions_table.php', 
    '2024_01_01_000023_create_survey_unsur_table.php' => '2024_01_01_000023_create_survey_unsur_table.php',
    '2024_01_01_000024_create_survey_archives_table.php' => '2024_01_01_000024_create_survey_archives_table.php',
];

// Actually, let me just do the dependency reordering properly
// survey_questions must come before survey_answers because survey_answers references it
// So the correct order should be:
// 000021_create_survey_questions_table.php (creates survey_questions)  
// 000025_create_survey_answers_table.php (references survey_questions via foreign key)

// Let me just fix the direct dependency - survey_answers needs to come AFTER survey_questions
echo "Reorganizing based on dependency: survey_answers must come after survey_questions\n";

$fixMap = [
    '2024_01_01_000021_create_survey_questions_table.php' => '2024_01_01_000021_create_survey_questions_table.php', // Keep in correct position
    '2024_01_01_000025_create_survey_answers_table.php' => '2024_01_01_000022_create_survey_answers_table.php', // Move to right after survey_questions
];

// Actually, let me just reorganize the survey files to fix the dependency properly
$actualFix = [
    '2024_01_01_000019_create_survey_responses_table.php' => '2024_01_01_000019_create_survey_responses_table.php', // This references tickets/surveys
    '2024_01_01_000021_create_survey_questions_table.php' => '2024_01_01_000020_create_survey_questions_table.php', // This is referenced by survey_answers
    '2024_01_01_000025_create_survey_answers_table.php' => '2024_01_01_000021_create_survey_answers_table.php', // This references survey_questions
    '2024_01_01_000022_create_survey_editions_table.php' => '2024_01_01_000022_create_survey_editions_table.php',
    '2024_01_01_000023_create_survey_unsur_table.php' => '2024_01_01_000023_create_survey_unsur_table.php',
    '2024_01_01_000024_create_survey_archives_table.php' => '2024_01_01_000024_create_survey_archives_table.php',
];

echo "Fixing survey dependency order...\n";

$successfulRenames = 0;
$filesToRename = [];

// Check if files exist and add to renaming queue
foreach ($actualFix as $oldName => $newName) {
    if ($oldName !== $newName) {  // Only rename if different
        $oldPath = $migrationDir . DIRECTORY_SEPARATOR . $oldName;
        $newPath = $migrationDir . DIRECTORY_SEPARATOR . $newName;
        
        if (file_exists($oldPath)) {
            $filesToRename[] = ['old'=>$oldPath, 'new'=>$newPath, 'oldName'=>$oldName, 'newName'=>$newName];
        }
    }
}

foreach ($filesToRename as $file) {
    if (rename($file['old'], $file['new'])) {
        echo "✓ Renamed: {$file['oldName']} -> {$file['newName']}\n";
        $successfulRenames++;
    } else {
        echo "✗ FAILED: Could not rename {$file['oldName']} to {$file['newName']}\n";
    }
}

echo "\nDependency fix completed: $successfulRenames files renamed.\n";

echo "\nNew proper sequence:\n";
echo "  1. 2024_01_01_000007_create_surveys_table.php\n";
echo "  2. 2024_01_01_000019_create_survey_responses_table.php\n";
echo "  3. 2024_01_01_000020_create_survey_questions_table.php (referenced by survey_answers)\n";
echo "  4. 2024_01_01_000021_create_survey_answers_table.php (references survey_questions)\n";
echo "  5. 2024_01_01_000022_create_survey_editions_table.php\n";
echo "  6. 2024_01_01_000023_create_survey_unsur_table.php\n";
echo "  7. 2024_01_01_000024_create_survey_archives_table.php\n";