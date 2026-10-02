<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Trigram indexes for the register search box (nomor, tujuan, perihal,
 * klasifikasi), which Filament runs as ILIKE '%term%' on PostgreSQL.
 */
return new class extends Migration
{
    private const COLUMNS = ['nomor_surat', 'tujuan_surat', 'perihal', 'klasifikasi'];

    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pg_trgm"');

        foreach (self::COLUMNS as $column) {
            DB::statement("CREATE INDEX IF NOT EXISTS idx_surat_keluar_{$column}_trgm ON surat_keluar USING GIN ({$column} gin_trgm_ops)");
        }
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $column) {
            DB::statement("DROP INDEX IF EXISTS idx_surat_keluar_{$column}_trgm");
        }
    }
};
