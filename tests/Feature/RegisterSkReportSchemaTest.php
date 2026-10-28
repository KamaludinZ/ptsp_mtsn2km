<?php

namespace Tests\Feature;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Indexes behind Unduh Laporan SK and Rekap Register SK, and one SK per stored scan. */
class RegisterSkReportSchemaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_10_28_100000_add_report_indexes_to_surat_keputusan.php');
    }

    /** @return array<string, string> index name => definition */
    private function indexes(): array
    {
        return DB::table('pg_indexes')->where('tablename', 'surat_keputusan')->pluck('indexdef', 'indexname')->all();
    }

    private function giveScan(string $nomorSk, string $path): void
    {
        DB::table('surat_keputusan')->where('nomor_sk', $nomorSk)
            ->update(['berkas_scan' => $path, 'berkas_scan_nama' => 'SK.pdf', 'scan_diunggah_pada' => now()]);
    }

    public function test_the_report_indexes_exist(): void
    {
        $indexes = $this->indexes();

        $this->assertStringContainsString('gin_trgm_ops', $indexes['idx_surat_keputusan_keterangan_trgm']);
        $this->assertStringContainsString('gin_trgm_ops', $indexes['idx_surat_keputusan_jenis_sk_trgm']);
        $this->assertStringContainsString('(tahun, created_at) WHERE (berkas_scan IS NULL)', $indexes['surat_keputusan_menunggu_scan_sejak_idx']);
        $this->assertStringContainsString('UNIQUE', $indexes['surat_keputusan_berkas_scan_unique']);
    }

    public function test_two_sk_cannot_share_a_stored_scan(): void
    {
        $year = now()->year;
        $this->giveScan("1 TAHUN {$year}", 'surat-keputusan/01J0000000000000000000SAMA.pdf');

        $this->expectException(UniqueConstraintViolationException::class);
        $this->giveScan("2 TAHUN {$year}", 'surat-keputusan/01J0000000000000000000SAMA.pdf');
    }

    public function test_the_migration_names_sk_already_sharing_a_scan(): void
    {
        $year = now()->year;
        $this->migration()->down();
        $this->assertArrayNotHasKey('surat_keputusan_berkas_scan_unique', $this->indexes());
        $this->giveScan("3 TAHUN {$year}", 'surat-keputusan/01J0000000000000000000SAMA.pdf');
        $this->giveScan("5 TAHUN {$year}", 'surat-keputusan/01J0000000000000000000SAMA.pdf');

        try {
            $this->migration()->up();
            $this->fail('The migration accepted SK sharing a scan.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString("3 TAHUN {$year}, 5 TAHUN {$year}", $e->getMessage());
        }

        DB::table('surat_keputusan')->where('nomor_sk', "5 TAHUN {$year}")
            ->update(['berkas_scan' => null, 'berkas_scan_nama' => null, 'scan_diunggah_pada' => null]);
        $this->migration()->up();
        $this->assertArrayHasKey('surat_keputusan_berkas_scan_unique', $this->indexes());
    }
}
