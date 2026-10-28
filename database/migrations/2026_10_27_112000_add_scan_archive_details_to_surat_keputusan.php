<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Arsip scan SK: what Rincian SK and the download need to know about the
 * stored scan. berkas_scan_mime is the type the upload was checked as
 * (PDF, JPEG or PNG), so the file is always served as that type; the size
 * and who uploaded it, when, belong to the current scan and go with it.
 * Scans already stored are filled in from their files.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->string('berkas_scan_mime', 50)->nullable()->after('berkas_scan_nama');
            $table->unsignedInteger('berkas_scan_ukuran')->nullable()->after('berkas_scan_mime');
            $table->timestamp('scan_diunggah_pada')->nullable()->after('berkas_scan_ukuran');
            $table->foreignId('scan_diunggah_oleh')->nullable()->after('scan_diunggah_pada')->constrained('users')->nullOnDelete();

            $table->index('scan_diunggah_oleh');
        });

        $this->fillStoredScans();

        $types = collect(self::MIME_TYPES)->map(fn (string $type) => "'{$type}'")->implode(', ');
        DB::statement("ALTER TABLE surat_keputusan ADD CONSTRAINT surat_keputusan_berkas_scan_mime_check CHECK (berkas_scan_mime IS NULL OR berkas_scan_mime IN ({$types}))");
        DB::statement('ALTER TABLE surat_keputusan ADD CONSTRAINT surat_keputusan_berkas_scan_ukuran_check CHECK (berkas_scan_ukuran IS NULL OR berkas_scan_ukuran BETWEEN 1 AND ' . self::MAX_BYTES . ')');
        // The details only exist with a scan, and a scan always says when it came in.
        DB::statement('ALTER TABLE surat_keputusan ADD CONSTRAINT surat_keputusan_scan_detail_check CHECK (CASE WHEN berkas_scan IS NULL'
            . ' THEN berkas_scan_mime IS NULL AND berkas_scan_ukuran IS NULL AND scan_diunggah_pada IS NULL AND scan_diunggah_oleh IS NULL'
            . ' ELSE scan_diunggah_pada IS NOT NULL END)');

        // Status "Menunggu scan" (filter, overview count): the SK still without a scan.
        DB::statement('CREATE INDEX IF NOT EXISTS surat_keputusan_menunggu_scan_idx ON surat_keputusan (tahun, nomor_urut) WHERE berkas_scan IS NULL');
    }

    /** Same as SuratKeputusan::SCAN_MIME_TYPES and SCAN_MAX_SIZE (10 MB) when this was written. */
    private const MIME_TYPES = ['application/pdf', 'image/jpeg', 'image/png'];

    private const MAX_BYTES = 10240 * 1024;

    private const MIME_BY_EXTENSION = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];

    private function fillStoredScans(): void
    {
        $disk = Storage::disk('local');

        DB::table('surat_keputusan')->whereNotNull('berkas_scan')->orderBy('id')
            ->each(function (object $sk) use ($disk) {
                $exists = $disk->exists($sk->berkas_scan);
                $mime = self::MIME_BY_EXTENSION[strtolower(pathinfo($sk->berkas_scan, PATHINFO_EXTENSION))]
                    ?? ($exists ? $disk->mimeType($sk->berkas_scan) : null);
                $size = $exists ? $disk->size($sk->berkas_scan) : null;

                DB::table('surat_keputusan')->where('id', $sk->id)->update([
                    'berkas_scan_mime' => in_array($mime, self::MIME_TYPES, true) ? $mime : null,
                    'berkas_scan_ukuran' => $size >= 1 && $size <= self::MAX_BYTES ? $size : null,
                    // The upload time was not kept before: the last change of the SK is the closest.
                    'scan_diunggah_pada' => $sk->updated_at ?? $sk->created_at ?? now(),
                ]);
            });
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS surat_keputusan_menunggu_scan_idx');
        DB::statement('ALTER TABLE surat_keputusan DROP CONSTRAINT IF EXISTS surat_keputusan_scan_detail_check');
        DB::statement('ALTER TABLE surat_keputusan DROP CONSTRAINT IF EXISTS surat_keputusan_berkas_scan_ukuran_check');
        DB::statement('ALTER TABLE surat_keputusan DROP CONSTRAINT IF EXISTS surat_keputusan_berkas_scan_mime_check');

        Schema::table('surat_keputusan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('scan_diunggah_oleh');
            $table->dropColumn(['berkas_scan_mime', 'berkas_scan_ukuran', 'scan_diunggah_pada']);
        });
    }
};
