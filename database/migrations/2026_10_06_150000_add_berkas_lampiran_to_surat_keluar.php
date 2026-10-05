<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Uploaded attachments of an outgoing letter: private file paths plus the
 * original file names (path => name). The existing `lampiran` text column
 * keeps the attachment description printed on the letter ("1 berkas").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->json('berkas_lampiran')->nullable();
            $table->json('berkas_lampiran_nama')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropColumn(['berkas_lampiran', 'berkas_lampiran_nama']);
        });
    }
};
