<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Sidik berkas scan (SHA-256 of the stored file, taken on upload): the
 * archive can be checked later for scans that went missing or changed on
 * the disk (sk:periksa-arsip). Scans already stored get theirs now.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->char('berkas_scan_sha256', 64)->nullable()->after('berkas_scan_ukuran');
        });
        DB::statement("ALTER TABLE surat_keputusan ADD CONSTRAINT surat_keputusan_berkas_scan_sha256_check CHECK (berkas_scan_sha256 IS NULL OR (berkas_scan IS NOT NULL AND berkas_scan_sha256 ~ '^[0-9a-f]{64}$'))");

        $disk = Storage::disk('local');
        DB::table('surat_keputusan')->whereNotNull('berkas_scan')->orderBy('id')->select('id', 'berkas_scan')->chunk(200, function ($rows) use ($disk) {
            foreach ($rows as $row) {
                if ($disk->exists($row->berkas_scan)) {
                    DB::table('surat_keputusan')->where('id', $row->id)->update(['berkas_scan_sha256' => hash_file('sha256', $disk->path($row->berkas_scan))]);
                }
            }
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE surat_keputusan DROP CONSTRAINT IF EXISTS surat_keputusan_berkas_scan_sha256_check');
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->dropColumn('berkas_scan_sha256');
        });
    }
};
