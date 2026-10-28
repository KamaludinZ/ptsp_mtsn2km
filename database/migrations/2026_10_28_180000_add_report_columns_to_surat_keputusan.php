<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Laporan & Evaluasi on the register itself, computed by the database so
 * every report reads them alike: bulan (of tanggal surat, for the recap per
 * month) and hari_sampai_terarsip (days from the number to the signed scan;
 * empty while the SK waits for it).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE surat_keputusan ADD COLUMN bulan smallint GENERATED ALWAYS AS (EXTRACT(MONTH FROM tanggal_surat)::smallint) STORED');
        DB::statement('ALTER TABLE surat_keputusan ADD COLUMN hari_sampai_terarsip numeric(8, 2) GENERATED ALWAYS AS (round((EXTRACT(EPOCH FROM (scan_diunggah_pada - created_at)) / 86400)::numeric, 2)) STORED');
        DB::statement('CREATE INDEX IF NOT EXISTS surat_keputusan_tahun_bulan_idx ON surat_keputusan (tahun, bulan)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS surat_keputusan_tahun_bulan_idx');
        DB::statement('ALTER TABLE surat_keputusan DROP COLUMN IF EXISTS hari_sampai_terarsip');
        DB::statement('ALTER TABLE surat_keputusan DROP COLUMN IF EXISTS bulan');
    }
};
