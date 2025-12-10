<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert all JSON columns to JSONB for better performance and GIN indexing support
     */
    public function up(): void
    {
        // Only run on PostgreSQL
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $conversions = [
            ['table' => 'activity_log', 'column' => 'properties'],
            ['table' => 'app_settings', 'column' => 'validation_rules'],
            ['table' => 'media', 'column' => 'custom_properties'],
            ['table' => 'media', 'column' => 'generated_conversions'],
            ['table' => 'media', 'column' => 'manipulations'],
            ['table' => 'media', 'column' => 'responsive_images'],
            ['table' => 'service_categories', 'column' => 'approval_roles'],
            ['table' => 'service_categories', 'column' => 'approval_users'],
            ['table' => 'service_categories', 'column' => 'backoffice_user_ids'],
            ['table' => 'service_categories', 'column' => 'disposition_user_ids'],
            ['table' => 'services', 'column' => 'approval_roles'],
            ['table' => 'services', 'column' => 'approval_users'],
            ['table' => 'services', 'column' => 'user_types_allowed'],
            ['table' => 'survey_archives', 'column' => 'calculated_values'],
            ['table' => 'survey_archives', 'column' => 'data'],
            ['table' => 'survey_editions', 'column' => 'settings'],
            ['table' => 'survey_questions', 'column' => 'options'],
            ['table' => 'survey_unsur', 'column' => 'metadata'],
            ['table' => 'whistleblowing', 'column' => 'evidence_files'],
        ];

        foreach ($conversions as $conversion) {
            $table = $conversion['table'];
            $column = $conversion['column'];

            // Check if table and column exist
            $exists = DB::select("
                SELECT 1
                FROM information_schema.columns
                WHERE table_name = ?
                AND column_name = ?
                AND data_type = 'json'
            ", [$table, $column]);

            if (!empty($exists)) {
                try {
                    echo "Converting {$table}.{$column} to JSONB...\n";
                    DB::statement("
                        ALTER TABLE {$table}
                        ALTER COLUMN {$column}
                        TYPE jsonb
                        USING {$column}::jsonb
                    ");
                } catch (\Exception $e) {
                    // Log but don't fail the migration
                    echo "Warning: Could not convert {$table}.{$column}: " . $e->getMessage() . "\n";
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only run on PostgreSQL
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $conversions = [
            ['table' => 'activity_log', 'column' => 'properties'],
            ['table' => 'app_settings', 'column' => 'validation_rules'],
            ['table' => 'media', 'column' => 'custom_properties'],
            ['table' => 'media', 'column' => 'generated_conversions'],
            ['table' => 'media', 'column' => 'manipulations'],
            ['table' => 'media', 'column' => 'responsive_images'],
            ['table' => 'service_categories', 'column' => 'approval_roles'],
            ['table' => 'service_categories', 'column' => 'approval_users'],
            ['table' => 'service_categories', 'column' => 'backoffice_user_ids'],
            ['table' => 'service_categories', 'column' => 'disposition_user_ids'],
            ['table' => 'services', 'column' => 'approval_roles'],
            ['table' => 'services', 'column' => 'approval_users'],
            ['table' => 'services', 'column' => 'user_types_allowed'],
            ['table' => 'survey_archives', 'column' => 'calculated_values'],
            ['table' => 'survey_archives', 'column' => 'data'],
            ['table' => 'survey_editions', 'column' => 'settings'],
            ['table' => 'survey_questions', 'column' => 'options'],
            ['table' => 'survey_unsur', 'column' => 'metadata'],
            ['table' => 'whistleblowing', 'column' => 'evidence_files'],
        ];

        foreach ($conversions as $conversion) {
            $table = $conversion['table'];
            $column = $conversion['column'];

            // Check if table and column exist
            $exists = DB::select("
                SELECT 1
                FROM information_schema.columns
                WHERE table_name = ?
                AND column_name = ?
                AND data_type = 'jsonb'
            ", [$table, $column]);

            if (!empty($exists)) {
                try {
                    DB::statement("
                        ALTER TABLE {$table}
                        ALTER COLUMN {$column}
                        TYPE json
                        USING {$column}::json
                    ");
                } catch (\Exception $e) {
                    // Log but don't fail the rollback
                    echo "Warning: Could not revert {$table}.{$column}: " . $e->getMessage() . "\n";
                }
            }
        }
    }
};
