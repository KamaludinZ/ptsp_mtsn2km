<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Entri SK: the scan fields kept consistent by the database. A scan lives
 * as one file directly in the private surat-keputusan directory (no other
 * folder, no "..") and "no scan" is NULL; its original name only exists
 * alongside a stored file.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE surat_keputusan ADD CONSTRAINT surat_keputusan_berkas_scan_check CHECK (berkas_scan IS NULL OR (berkas_scan ~ '^surat-keputusan/[^/]+$' AND position('..' in berkas_scan) = 0))");
        DB::statement('ALTER TABLE surat_keputusan ADD CONSTRAINT surat_keputusan_berkas_scan_nama_check CHECK (berkas_scan_nama IS NULL OR berkas_scan IS NOT NULL)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE surat_keputusan DROP CONSTRAINT IF EXISTS surat_keputusan_berkas_scan_nama_check');
        DB::statement('ALTER TABLE surat_keputusan DROP CONSTRAINT IF EXISTS surat_keputusan_berkas_scan_check');
    }
};
