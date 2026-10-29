<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Register Penomoran SK: SK numbers "{N} TAHUN {YYYY}", 1-9999 per year,
 * a sequence of its own apart from surat keluar. Each row is one SK with
 * the officer who asked for it (and the active role they acted in) and an
 * optional scan on the private disk. jenis_sk keeps the Master Persuratan
 * name the SK was saved with, so renaming a jenis leaves old SK as they are.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keputusan', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedSmallInteger('nomor_urut');
            $table->string('nomor_sk', 30)->unique();
            $table->date('tanggal_surat');
            $table->string('perihal');
            $table->string('jenis_sk');
            $table->string('nama_peminta');
            $table->text('keterangan')->nullable();
            $table->string('berkas_scan')->nullable();
            $table->string('berkas_scan_nama')->nullable();
            $table->foreignId('pembuat_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('peran_pembuat', 50)->nullable();
            $table->timestamps();

            $table->unique(['tahun', 'nomor_urut']);
            $table->index(['tahun', 'jenis_sk']);
            $table->index('tanggal_surat');
            $table->index('pembuat_id');
        });

        // The register's rules, kept by the database too: 1-9999 per year, the
        // number spelled "{N} TAHUN {YYYY}", the letter dated in its SK year,
        // and the describing fields never blank.
        DB::statement('ALTER TABLE surat_keputusan ADD CONSTRAINT surat_keputusan_nomor_urut_check CHECK (nomor_urut BETWEEN 1 AND 9999)');
        DB::statement("ALTER TABLE surat_keputusan ADD CONSTRAINT surat_keputusan_nomor_sk_check CHECK (nomor_sk = nomor_urut::text || ' TAHUN ' || tahun::text)");
        DB::statement('ALTER TABLE surat_keputusan ADD CONSTRAINT surat_keputusan_tahun_check CHECK (tahun = EXTRACT(YEAR FROM tanggal_surat))');
        DB::statement("ALTER TABLE surat_keputusan ADD CONSTRAINT surat_keputusan_isian_check CHECK (btrim(perihal) <> '' AND btrim(jenis_sk) <> '' AND btrim(nama_peminta) <> '')");

        // Trigram indexes for the register search box (ILIKE '%term%').
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pg_trgm"');
        foreach (self::SEARCH_COLUMNS as $column) {
            DB::statement("CREATE INDEX IF NOT EXISTS idx_surat_keputusan_{$column}_trgm ON surat_keputusan USING GIN ({$column} gin_trgm_ops)");
        }
    }

    private const SEARCH_COLUMNS = ['nomor_sk', 'perihal', 'nama_peminta'];

    public function down(): void
    {
        Schema::dropIfExists('surat_keputusan');
    }
};
