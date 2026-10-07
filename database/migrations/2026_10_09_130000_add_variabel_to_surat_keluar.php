<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nilai variabel tambahan ({v}/{V}) yang diisi petugas saat meminta nomor,
 * disimpan agar nomor dapat dirangkai ulang sama persis saat surat dilengkapi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->string('variabel', 50)->nullable()->after('klasifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropColumn('variabel');
        });
    }
};
