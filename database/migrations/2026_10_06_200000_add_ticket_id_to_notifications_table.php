<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Notifikasi ↔ permohonan: a real foreign key, backfilled from the stored ticket number. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('ticket_id')->nullable()->after('notifiable_id')->constrained()->nullOnDelete();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                update notifications n set ticket_id = t.id
                from tickets t
                where n.ticket_id is null
                  and t.ticket_number = coalesce(n.data->'viewData'->>'ticket_number', n.data->>'ticket_number')
            SQL);
        }
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ticket_id');
        });
    }
};
