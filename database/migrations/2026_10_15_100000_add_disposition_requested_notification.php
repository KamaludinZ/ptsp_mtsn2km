<?php

use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Notifikasi "permohonan menunggu disposisi" ke Kepala Madrasah dan/atau
 * Kepala TU lewat email dan WhatsApp: tambahkan templatnya (seeder hanya
 * menambah yang belum ada; templat yang sudah diubah admin tidak disentuh).
 */
return new class extends Migration
{
    public function up(): void
    {
        (new NotificationTemplateSeeder())->run();
    }

    public function down(): void
    {
        DB::table('notification_templates')->where('key', 'disposition_requested')->delete();
    }
};
