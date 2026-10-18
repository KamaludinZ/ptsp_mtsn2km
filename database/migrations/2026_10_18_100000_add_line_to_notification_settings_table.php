<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Jalur sambungan notifikasi: the pelayanan line (permohonan layanan) and the
 * pengaduan line (pengaduan, saran & WBS) each have their own Email and
 * WhatsApp gateway, so a channel is unique per line. Existing rows are the
 * pelayanan line.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_settings', function (Blueprint $table) {
            $table->string('line', 20)->default('pelayanan');
            $table->dropUnique(['channel']);
            $table->unique(['line', 'channel']);
        });
    }

    public function down(): void
    {
        DB::table('notification_settings')->where('line', '!=', 'pelayanan')->delete();

        Schema::table('notification_settings', function (Blueprint $table) {
            $table->dropUnique(['line', 'channel']);
            $table->dropColumn('line');
            $table->unique('channel');
        });
    }
};
