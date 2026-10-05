<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Notifikasi in-app: indexes for the clean-up (created_at) and for finding
 * a request's notifications by its number (data.viewData.ticket_number).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->index('created_at');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("create index notifications_ticket_number_index on notifications ((data->'viewData'->>'ticket_number'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('drop index if exists notifications_ticket_number_index');
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
