<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enable extensions that might be needed for production
        // Only run on PostgreSQL connections
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
            DB::statement('CREATE EXTENSION IF NOT EXISTS "pg_trgm"'); // For text similarity queries
            DB::statement('CREATE EXTENSION IF NOT EXISTS "btree_gin"'); // For indexing JSON fields
        }

        // Update sequences for existing tables to ensure proper PostgreSQL sequences
        $this->updatePostgreSqlSequences();

        // Optimize tables for PostgreSQL
        $this->optimizeTableIndexes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only run on PostgreSQL connections
        if (DB::getDriverName() === 'pgsql') {
            // Disable extensions (be careful - this will remove functionality)
            DB::statement('DROP EXTENSION IF EXISTS "uuid-ossp" CASCADE');
            DB::statement('DROP EXTENSION IF EXISTS "pg_trgm" CASCADE');
            DB::statement('DROP EXTENSION IF EXISTS "btree_gin" CASCADE');
        }
    }

    /**
     * Update sequences for tables that use bigIncrements
     */
    private function updatePostgreSqlSequences(): void
    {
        $tables = [
            'users', 'services', 'service_categories', 'service_components',
            'service_requirements', 'surveys', 'survey_answers', 'survey_questions',
            'tickets', 'ticket_files', 'ticket_logs', 'ticket_outputs',
            'ticket_workflows', 'ticket_workflow_steps', 'workflows', 'workflow_steps',
            'permissions', 'roles', 'model_has_permissions', 'model_has_roles',
            'role_has_permissions', 'personal_access_tokens', 'sessions', 'cache',
            'media', 'activity_log', 'app_settings', 'hero_sliders', 'registration_codes',
            'pengumumen', 'survey_editions', 'survey_unsur', 'survey_archives',
            'faqs', 'pengumuman_views', 'complaints'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'id')) {
                // Update sequence to current max ID value - only on PostgreSQL
                if (DB::getDriverName() === 'pgsql') {
                    DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), coalesce(max(id)::bigint, 0) + 1, false) FROM {$table};");
                }
            }
        }
    }

    /**
     * Optimize table indexes for PostgreSQL
     */
    private function optimizeTableIndexes(): void
    {
        // Add GIN indexes for JSON columns to improve query performance - only on PostgreSQL
        if (DB::getDriverName() !== 'pgsql') {
            return; // Skip if not PostgreSQL
        }

        $jsonColumns = [
            ['table' => 'services', 'column' => 'user_types_allowed'],
            ['table' => 'app_settings', 'column' => 'validation_rules'],
            ['table' => 'survey_archives', 'column' => 'data'],
            ['table' => 'survey_archives', 'column' => 'calculated_values'],
            ['table' => 'activity_log', 'column' => 'properties'],
            ['table' => 'survey_unsur', 'column' => 'metadata'],
            ['table' => 'media', 'column' => 'manipulations'],
            ['table' => 'media', 'column' => 'custom_properties'],
            ['table' => 'media', 'column' => 'generated_conversions'],
            ['table' => 'media', 'column' => 'responsive_images'],
            ['table' => 'survey_editions', 'column' => 'settings'],
            ['table' => 'services', 'column' => 'approval_roles'],
            ['table' => 'services', 'column' => 'approval_users'],
            ['table' => 'survey_questions', 'column' => 'options'],
        ];

        foreach ($jsonColumns as $config) {
            if (Schema::hasTable($config['table']) && Schema::hasColumn($config['table'], $config['column'])) {
                $tableName = $config['table'];
                $columnName = $config['column'];

                // Check if GIN index already exists
                $indexExists = DB::select("
                    SELECT 1 FROM pg_indexes
                    WHERE tablename = ?
                    AND indexdef LIKE '%GIN%'
                    AND indexdef LIKE ?
                ", [$tableName, "%{$columnName}%"]);

                if (empty($indexExists)) {
                    // For JSON columns we'll skip the GIN index creation since they require special operator classes
                    // and instead rely on the PostgreSQL jsonb extension features when actually needed
                    // For now we'll just skip JSON-based columns
                    $result = DB::select("
                        SELECT data_type
                        FROM information_schema.columns
                        WHERE table_name = ? AND column_name = ?
                    ", [$tableName, $columnName]);

                    if (!empty($result)) {
                        $dataType = strtolower($result[0]->data_type);

                        if (strpos($dataType, 'jsonb') !== false) {
                            // JSONB column - can use GIN index with jsonb_path_ops
                            try {
                                DB::statement("CREATE INDEX IF NOT EXISTS idx_{$tableName}_{$columnName}_gin ON {$tableName} USING GIN ({$columnName} jsonb_path_ops)");
                            } catch (\Exception $e) {
                                // Silently skip if index creation fails
                            }
                        } elseif (strpos($dataType, 'json') !== false) {
                            // JSON column (not JSONB) - skip GIN index creation
                            // JSON type doesn't support GIN indexes directly
                            // We would need to cast to JSONB or convert the column
                            // For now, skip to avoid errors
                        } else {
                            // Text column - use gin_trgm_ops for text similarity
                            try {
                                DB::statement("CREATE INDEX IF NOT EXISTS idx_{$tableName}_{$columnName}_gin ON {$tableName} USING GIN ({$columnName} gin_trgm_ops)");
                            } catch (\Exception $e) {
                                // Silently skip if index creation fails
                            }
                        }
                    }
                }
            }
        }
    }
};