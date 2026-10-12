<?php

use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Notifikasi pengaduan, saran & WBS: the riwayat notifikasi can point at a
 * report, and the three report events get their email and WhatsApp
 * templates (the seeder only adds missing ones).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_deliveries', function (Blueprint $table) {
            $table->foreignId('complaint_id')->nullable()->after('ticket_id')->constrained()->nullOnDelete();
        });

        (new NotificationTemplateSeeder())->run();
    }

    public function down(): void
    {
        DB::table('notification_templates')->whereIn('key', ['complaint_received', 'complaint_new', 'complaint_updated'])->delete();

        Schema::table('notification_deliveries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('complaint_id');
        });
    }
};
