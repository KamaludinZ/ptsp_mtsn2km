<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel permohonan (tickets) for Kelola Permohonan:
 * - indexes for the list's tabs, filters and sorting;
 * - foreign keys that never delete requests with a user or service:
 *   applicant and service RESTRICT, officers SET NULL (history stays);
 * - CHECK constraints on status, mode, priority, approval and incoming
 *   category (NOT VALID: enforced for new writes, old rows untouched).
 */
return new class extends Migration
{
    private const CHECKS = [
        'tickets_status_check' => "status in ('submitted','verified','in_process','approved','completed','rejected','cancelled')",
        'tickets_mode_check' => "mode in ('online','offline','hybrid')",
        'tickets_priority_check' => "priority in ('low','normal','high','urgent')",
        'tickets_approval_status_check' => "approval_status in ('pending','approved','rejected')",
        'tickets_incoming_category_check' => "incoming_category is null or incoming_category in ('disposisi','tembusan','koordinasi','arahan')",
    ];

    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'tickets_status_created_at_index');
            $table->index('service_id', 'tickets_service_id_index');
            $table->index('user_id', 'tickets_user_id_index');
            $table->index('assigned_to_id', 'tickets_assigned_to_id_index');
            $table->index('created_at', 'tickets_created_at_index');
            $table->index('estimated_completion_date', 'tickets_estimated_completion_date_index');
            $table->index(['approval_required', 'approval_status'], 'tickets_approval_index');
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('alter table tickets alter column created_by drop not null');

        foreach ([
            ['user_id', 'users', 'restrict'],
            ['service_id', 'services', 'restrict'],
            ['assigned_to_id', 'users', 'set null'],
            ['current_handler_id', 'users', 'set null'],
            ['created_by', 'users', 'set null'],
        ] as [$column, $table, $onDelete]) {
            DB::statement("alter table tickets drop constraint if exists tickets_{$column}_foreign");
            DB::statement("alter table tickets add constraint tickets_{$column}_foreign foreign key ({$column}) references {$table}(id) on delete {$onDelete}");
        }

        DB::statement('create index if not exists tickets_disposition_recipients_index on tickets using gin (disposition_recipients)');

        foreach (self::CHECKS as $name => $check) {
            DB::statement("alter table tickets drop constraint if exists {$name}");
            DB::statement("alter table tickets add constraint {$name} check ({$check}) not valid");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            foreach (array_keys(self::CHECKS) as $name) {
                DB::statement("alter table tickets drop constraint if exists {$name}");
            }
            DB::statement('drop index if exists tickets_disposition_recipients_index');

            foreach (['user_id', 'service_id', 'assigned_to_id', 'current_handler_id', 'created_by'] as $column) {
                DB::statement("alter table tickets drop constraint if exists tickets_{$column}_foreign");
                DB::statement("alter table tickets add constraint tickets_{$column}_foreign foreign key ({$column}) references " . (str_ends_with($column, 'service_id') ? 'services' : 'users') . '(id) on delete cascade');
            }
        }

        Schema::table('tickets', function (Blueprint $table) {
            foreach (['tickets_status_created_at_index', 'tickets_service_id_index', 'tickets_user_id_index', 'tickets_assigned_to_id_index',
                'tickets_created_at_index', 'tickets_estimated_completion_date_index', 'tickets_approval_index'] as $index) {
                $table->dropIndex($index);
            }
        });
    }
};
