<?php

use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The "Pengingat survei kepuasan" notification (survey_reminder) needs its
 * email and WhatsApp templates on existing installations too. The seeder
 * only adds missing templates, so edited ones are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new NotificationTemplateSeeder())->run();
    }

    public function down(): void
    {
        DB::table('notification_templates')->where('key', 'survey_reminder')->delete();
    }
};
