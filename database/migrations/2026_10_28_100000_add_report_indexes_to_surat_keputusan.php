<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Unduh Laporan SK and Rekap Register SK on the database: the register
 * search (and so the downloaded report) also looks in keterangan, where an
 * SK asked for from a ticket keeps the ticket number, and in jenis SK; the
 * recap lists the SK waiting longest for their scan. A stored scan belongs
 * to one SK only, so replacing or deleting it can never take another SK's.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pg_trgm"');
        foreach (self::SEARCH_COLUMNS as $column) {
            DB::statement("CREATE INDEX IF NOT EXISTS idx_surat_keputusan_{$column}_trgm ON surat_keputusan USING GIN ({$column} gin_trgm_ops)");
        }

        // Menunggu scan paling lama, and the SK past SkRegisterRecap::SCAN_DUE_DAYS.
        DB::statement('CREATE INDEX IF NOT EXISTS surat_keputusan_menunggu_scan_sejak_idx ON surat_keputusan (tahun, created_at) WHERE berkas_scan IS NULL');

        $shared = DB::table('surat_keputusan')->whereNotNull('berkas_scan')
            ->select('berkas_scan')->groupBy('berkas_scan')->havingRaw('count(*) > 1')->pluck('berkas_scan');
        if ($shared->isNotEmpty()) {
            $numbers = DB::table('surat_keputusan')->whereIn('berkas_scan', $shared)->orderBy('tahun')->orderBy('nomor_urut')->pluck('nomor_sk')->implode(', ');

            throw new RuntimeException("Beberapa SK memakai berkas scan yang sama ({$numbers}). Unggah ulang scan SK yang keliru lewat \"Ubah\", lalu jalankan migrasi ini lagi.");
        }
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS surat_keputusan_berkas_scan_unique ON surat_keputusan (berkas_scan) WHERE berkas_scan IS NOT NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS surat_keputusan_berkas_scan_unique');
        DB::statement('DROP INDEX IF EXISTS surat_keputusan_menunggu_scan_sejak_idx');
        foreach (self::SEARCH_COLUMNS as $column) {
            DB::statement("DROP INDEX IF EXISTS idx_surat_keputusan_{$column}_trgm");
        }
    }

    /** Searched besides nomor_sk, perihal and nama_peminta (indexed with the table). */
    private const SEARCH_COLUMNS = ['keterangan', 'jenis_sk'];
};
