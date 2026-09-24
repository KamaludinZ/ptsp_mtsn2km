<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * These five child tables had their ticket_id foreign key pointing at
     * "tickets_backup" (an empty leftover table from a prior rename) instead
     * of "tickets". Since tickets_backup has zero rows, every insert that
     * referenced a real ticket was silently rejected by Postgres — ticket
     * file uploads, logs, outputs, workflow tracking, and survey responses
     * tied to a ticket could never actually be saved.
     *
     * Only the pgsql branch of the 2025_11_06 tickets migration renames the
     * old table to tickets_backup; SQLite rebuilds and MySQL alters in place,
     * so their foreign keys already point at "tickets" (and SQLite cannot
     * drop foreign keys by name anyway).
     */
    private array $cascadeTables = ['ticket_workflows', 'ticket_files', 'ticket_logs', 'ticket_outputs'];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->cascadeTables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropForeign("{$table}_ticket_id_foreign");
                $blueprint->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnDelete();
            });
        }

        Schema::table('survey_responses', function (Blueprint $blueprint) {
            $blueprint->dropForeign('survey_responses_ticket_id_foreign');
            $blueprint->foreign('ticket_id')->references('id')->on('tickets')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->cascadeTables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['ticket_id']);
                $blueprint->foreign('ticket_id')->references('id')->on('tickets_backup')->cascadeOnDelete();
            });
        }

        Schema::table('survey_responses', function (Blueprint $blueprint) {
            $blueprint->dropForeign(['ticket_id']);
            $blueprint->foreign('ticket_id')->references('id')->on('tickets_backup')->nullOnDelete();
        });
    }
};
