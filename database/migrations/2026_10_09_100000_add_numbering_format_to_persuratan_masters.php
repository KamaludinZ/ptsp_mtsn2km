<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penomoran Otomatis: format nomor surat keluar disimpan pada baris jenis
 * surat (persuratan_masters type "jenis_surat") sebagai satu-satunya acuan.
 * format_nomor kosong = format bawaan (NomorFormat::DEFAULT); mode_bulan
 * menentukan {M} angka Arab atau Romawi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persuratan_masters', function (Blueprint $table) {
            $table->string('format_nomor', 120)->nullable()->after('nama');
            $table->string('mode_bulan', 10)->default('arab')->after('format_nomor');
        });
    }

    public function down(): void
    {
        Schema::table('persuratan_masters', function (Blueprint $table) {
            $table->dropColumn(['format_nomor', 'mode_bulan']);
        });
    }
};
