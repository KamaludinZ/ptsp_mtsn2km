<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Token {S}: singkatan unit kerja diambil dari Pengaturan Aplikasi
 * (surat_kode_satker); jenis surat yang butuh singkatan berbeda mengisi
 * singkatan_unit_kerja sebagai penimpaan. Kosong = pakai Pengaturan Aplikasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persuratan_masters', function (Blueprint $table) {
            $table->string('singkatan_unit_kerja', 30)->nullable()->after('mode_bulan');
        });
    }

    public function down(): void
    {
        Schema::table('persuratan_masters', function (Blueprint $table) {
            $table->dropColumn('singkatan_unit_kerja');
        });
    }
};
