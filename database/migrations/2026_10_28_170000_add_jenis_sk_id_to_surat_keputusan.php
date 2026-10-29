<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Master Jenis SK behind each SK, for Laporan & Evaluasi: the SK keeps the
 * jenis as it was written (jenis_sk), and jenis_sk_id keeps which master
 * entry that was, so a jenis renamed in Master Persuratan still counts its
 * older SK. Filled from the names already recorded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->foreignId('jenis_sk_id')->nullable()->after('jenis_sk')->constrained('persuratan_masters')->nullOnDelete();
            $table->index(['tahun', 'jenis_sk_id']);
        });

        DB::statement(<<<'SQL'
            UPDATE surat_keputusan sk SET jenis_sk_id = m.id
            FROM persuratan_masters m
            WHERE m.type = 'jenis_sk' AND lower(btrim(m.nama)) = lower(btrim(sk.jenis_sk))
            SQL);
    }

    public function down(): void
    {
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->dropIndex(['tahun', 'jenis_sk_id']);
            $table->dropConstrainedForeignId('jenis_sk_id');
        });
    }
};
