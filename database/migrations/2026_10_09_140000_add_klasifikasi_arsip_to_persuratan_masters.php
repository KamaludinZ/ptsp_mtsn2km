<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Klasifikasi arsip bawaan per jenis surat: mengisi token {k}/{K} bila
 * petugas tidak memilih klasifikasi saat meminta nomor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persuratan_masters', function (Blueprint $table) {
            $table->string('klasifikasi_arsip', 50)->nullable()->after('mode_bulan');
        });
    }

    public function down(): void
    {
        Schema::table('persuratan_masters', function (Blueprint $table) {
            $table->dropColumn('klasifikasi_arsip');
        });
    }
};
