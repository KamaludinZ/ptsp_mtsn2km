<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Token {v}/{V}: definisi variabel tambahan per jenis surat (label, arti,
 * wajib diisi). Petugas mengisi nilainya saat meminta nomor. Kosong = jenis
 * surat ini tidak memakai variabel tambahan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persuratan_masters', function (Blueprint $table) {
            $table->jsonb('variabel_nomor')->nullable()->after('singkatan_unit_kerja');
        });
    }

    public function down(): void
    {
        Schema::table('persuratan_masters', function (Blueprint $table) {
            $table->dropColumn('variabel_nomor');
        });
    }
};
