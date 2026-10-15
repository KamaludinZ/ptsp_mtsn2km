<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for columns that lists, badges and ticket pages filter on often.
 * PostgreSQL does not index foreign keys by itself.
 */
return new class extends Migration
{
    private const INDEXES = [
        'ticket_files_ticket_id_index' => ['ticket_files', ['ticket_id']],
        'ticket_outputs_ticket_id_index' => ['ticket_outputs', ['ticket_id']],
        'complaints_status_created_at_index' => ['complaints', ['status', 'created_at']],
        'complaints_complaint_type_index' => ['complaints', ['complaint_type']],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $name => [$table, $columns]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumns($table, $columns)) {
                continue;
            }

            if (DB::getDriverName() === 'pgsql') {
                DB::statement(sprintf('CREATE INDEX IF NOT EXISTS %s ON %s (%s)', $name, $table, implode(', ', $columns)));
            } elseif (! Schema::hasIndex($table, $name)) {
                Schema::table($table, fn ($t) => $t->index($columns, $name));
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $name => [$table]) {
            if (Schema::hasTable($table) && Schema::hasIndex($table, $name)) {
                Schema::table($table, fn ($t) => $t->dropIndex($name));
            }
        }
    }
};
