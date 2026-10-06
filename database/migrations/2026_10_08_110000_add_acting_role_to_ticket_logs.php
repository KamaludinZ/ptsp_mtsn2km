<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Konteks peran aktif pada riwayat layanan: atas nama peran apa petugas
 * mengambil tiap aksi (ActiveRoles), terpisah dari peran-peran yang ia
 * pegang. Baris lama tetap kosong; menambah kolom tidak mengubah baris
 * sehingga trigger immutable ticket_logs tidak terpicu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_logs', function (Blueprint $table) {
            $table->string('acting_role', 125)->nullable()->after('performed_by');
            $table->index(['acting_role', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('ticket_logs', function (Blueprint $table) {
            $table->dropIndex(['acting_role', 'created_at']);
            $table->dropColumn('acting_role');
        });
    }
};
