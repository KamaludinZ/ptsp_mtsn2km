<?php

namespace Tests\Feature;

use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Sidik berkas scan and the archive check: scans that went missing or changed on the disk come to light. */
class RegisterSkArchiveCheckTest extends TestCase
{
    use RefreshDatabase;

    private const CONTENT = '%PDF-1.4 SK wali kelas';

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->actingAs($this->officer);
    }

    private function archive(int $nomorUrut): SuratKeputusan
    {
        $sk = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', $nomorUrut)->firstOrFail();

        return app(SuratKeputusanService::class)->attachScan($sk, UploadedFile::fake()->createWithContent("SK {$nomorUrut}.pdf", self::CONTENT . $nomorUrut));
    }

    public function test_an_upload_keeps_the_sidik_of_the_file(): void
    {
        $sk = $this->archive(1);

        $this->assertSame(hash('sha256', self::CONTENT . '1'), $sk->berkas_scan_sha256);
        $this->assertNull(app(SuratKeputusanService::class)->removeScan($sk)->berkas_scan_sha256);
    }

    public function test_the_check_finds_missing_changed_and_unchecked_scans(): void
    {
        $missing = $this->archive(1);
        $changed = $this->archive(2);
        $unchecked = $this->archive(3);
        $this->archive(4);
        Storage::disk('local')->delete($missing->berkas_scan);
        Storage::disk('local')->put($changed->berkas_scan, 'isi lain');
        DB::table('surat_keputusan')->where('id', $unchecked->id)->update(['berkas_scan_sha256' => null]);

        $result = SuratKeputusanService::verifyArchive(now()->year);

        $this->assertSame(4, $result['diperiksa']);
        $this->assertSame(1, $result['utuh']);
        $this->assertSame(['hilang', 'berubah', 'tanpa_sidik'], array_column($result['masalah'], 'masalah'));
        $this->assertSame($missing->nomor_sk, $result['masalah'][0]['nomor_sk']);
        $this->assertSame(['diperiksa' => 0, 'utuh' => 0, 'masalah' => []], SuratKeputusanService::verifyArchive(now()->year - 1));
    }

    public function test_the_command_reports_and_fails_on_a_problem(): void
    {
        $sk = $this->archive(1);
        $this->artisan('sk:periksa-arsip')->expectsOutput('1 scan diperiksa, 1 utuh.')->assertSuccessful();

        Storage::disk('local')->delete($sk->berkas_scan);
        $this->artisan('sk:periksa-arsip', ['--tahun' => now()->year])
            ->expectsOutput('1 scan diperiksa, 0 utuh.')
            ->expectsTable(['Nomor SK', 'Masalah'], [[$sk->nomor_sk, 'Berkas scan tidak ada di arsip']])
            ->assertFailed();
    }

    public function test_the_api_checks_the_archive_for_the_back_office(): void
    {
        $sk = $this->archive(1);
        Storage::disk('local')->put($sk->berkas_scan, 'isi lain');
        Sanctum::actingAs($this->officer, ['penomoran-sk:baca']);

        $this->getJson('/api/penomoran-sk/arsip/periksa')->assertOk()
            ->assertJsonPath('tahun', 'semua')
            ->assertJsonPath('diperiksa', 1)
            ->assertJsonPath('masalah.0.masalah', 'berubah');
        $this->getJson("/api/penomoran-sk/{$sk->id}")->assertOk()->assertJsonPath('arsip.sha256', hash('sha256', self::CONTENT . '1'));

        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail(), ['*']);
        $this->getJson('/api/penomoran-sk/arsip/periksa')->assertForbidden();
    }

    public function test_the_migration_takes_the_sidik_of_scans_already_stored(): void
    {
        $sk = $this->archive(1);
        $migration = require database_path('migrations/2026_10_28_190000_add_scan_hash_to_surat_keputusan.php');
        $migration->down();
        $migration->up();

        $this->assertSame(hash('sha256', self::CONTENT . '1'), $sk->fresh()->berkas_scan_sha256);
    }
}
