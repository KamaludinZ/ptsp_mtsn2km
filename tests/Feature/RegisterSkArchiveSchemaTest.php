<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Arsip scan SK in the database: the checked type, size and uploader of the current scan. */
class RegisterSkArchiveSchemaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function first(): SuratKeputusan
    {
        return SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 1)->firstOrFail();
    }

    private function row(array $override = []): array
    {
        return [
            'tahun' => 2026, 'nomor_urut' => 70, 'nomor_sk' => '70 TAHUN 2026', 'tanggal_surat' => '2026-05-04',
            'perihal' => 'Panitia penerimaan peserta didik baru', 'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan', 'nama_peminta' => 'Waka Kesiswaan',
            'created_at' => now(), 'updated_at' => now(),
            ...$override,
        ];
    }

    private function assertRefused(array $override): void
    {
        try {
            DB::transaction(fn () => DB::table('surat_keputusan')->insert($this->row($override)));
        } catch (QueryException) {
            $this->assertTrue(true);

            return;
        }

        $this->fail('Row was accepted: ' . json_encode($override));
    }

    public function test_table_keeps_the_scan_details(): void
    {
        $this->assertTrue(Schema::hasColumns('surat_keputusan', ['berkas_scan_mime', 'berkas_scan_ukuran', 'scan_diunggah_pada', 'scan_diunggah_oleh']));
        $this->assertNotNull(DB::selectOne("select 1 from pg_indexes where tablename = 'surat_keputusan' and indexname = 'surat_keputusan_menunggu_scan_idx'"));
    }

    public function test_the_database_keeps_the_details_with_their_scan(): void
    {
        $scan = ['berkas_scan' => 'surat-keputusan/01JB7S2K.pdf', 'scan_diunggah_pada' => now()];

        $this->assertRefused([...$scan, 'berkas_scan_mime' => 'text/html']);
        $this->assertRefused([...$scan, 'berkas_scan_ukuran' => 0]);
        $this->assertRefused([...$scan, 'berkas_scan_ukuran' => 10240 * 1024 + 1]);
        $this->assertRefused(['berkas_scan' => 'surat-keputusan/01JB7S2K.pdf']);
        $this->assertRefused(['berkas_scan_mime' => 'application/pdf']);
        $this->assertRefused(['scan_diunggah_pada' => now()]);
        $this->assertRefused(['scan_diunggah_oleh' => $this->user('staff1@mtsn2malang.sch.id')->id]);

        DB::table('surat_keputusan')->insert($this->row([...$scan, 'berkas_scan_mime' => 'application/pdf', 'berkas_scan_ukuran' => 10240 * 1024]));
        $this->assertSame('PDF', SuratKeputusan::where('nomor_urut', 70)->firstOrFail()->scanType());
    }

    public function test_uploading_records_the_type_size_and_uploader(): void
    {
        $this->actingAs($officer = $this->user('staff1@mtsn2malang.sch.id'));
        $this->freezeSecond();

        app(SuratKeputusanService::class)->attachScan($sk = $this->first(), UploadedFile::fake()->image('SK wali kelas.png')->size(200));

        $sk->refresh();
        $this->assertSame('image/png', $sk->berkas_scan_mime);
        $this->assertSame(Storage::disk('local')->size($sk->berkas_scan), $sk->berkas_scan_ukuran);
        $this->assertTrue($sk->scan_diunggah_pada->eq(now()));
        $this->assertSame($officer->id, $sk->scan_diunggah_oleh);
        $this->assertSame($officer->id, $sk->pengunggahScan->id);
    }

    public function test_replacing_the_scan_replaces_its_details_and_removing_clears_them(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $service = app(SuratKeputusanService::class);
        $service->attachScan($sk = $this->first(), UploadedFile::fake()->create('draf.pdf', 300, 'application/pdf'));

        $this->actingAs($admin = $this->user('ptsp@mtsn2malang.sch.id'));
        $this->travel(1)->hour();
        $service->attachScan($sk->fresh(), UploadedFile::fake()->image('SK.jpg'));

        $sk->refresh();
        $this->assertSame('image/jpeg', $sk->berkas_scan_mime);
        $this->assertSame($admin->id, $sk->scan_diunggah_oleh);
        $this->assertTrue($sk->scan_diunggah_pada->isSameMinute(now()));

        // Other corrections leave the scan's details alone.
        $this->travel(1)->hour();
        $service->describe($sk, ['nama_peminta' => 'Kepala Madrasah']);
        $this->assertFalse($sk->fresh()->scan_diunggah_pada->isSameHour(now()));

        $service->removeScan($sk->fresh());
        $this->assertSame([null, null, null, null], array_values($sk->fresh()->only(['berkas_scan_mime', 'berkas_scan_ukuran', 'scan_diunggah_pada', 'scan_diunggah_oleh'])));
    }

    public function test_the_panel_names_the_stored_scan_after_its_checked_type(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        // A PDF the browser called ".html" is stored and served as the PDF it was checked as.
        Livewire::test(ListSuratKeputusan::class)
            ->callTableAction('edit', $sk = $this->first(), ['berkas_scan' => UploadedFile::fake()->create('SK final.html', 100, 'application/pdf')])
            ->assertHasNoTableActionErrors();

        $sk->refresh();
        $this->assertStringEndsWith('.pdf', $sk->berkas_scan);
        $this->assertSame('application/pdf', $sk->berkas_scan_mime);
        $this->assertSame('SK final.html', $sk->berkas_scan_nama);
    }

    public function test_an_uploader_account_removed_for_good_leaves_the_scan(): void
    {
        $uploader = User::factory()->create(['user_type' => 'pegawai']);
        $this->actingAs($uploader);
        app(SuratKeputusanService::class)->attachScan($sk = $this->first(), UploadedFile::fake()->create('SK.pdf', 100, 'application/pdf'));

        $uploader->forceDelete();

        $this->assertNull($sk->fresh()->scan_diunggah_oleh);
        $this->assertTrue($sk->fresh()->hasScan());
    }

    public function test_migration_fills_in_scans_already_stored(): void
    {
        $migration = require database_path('migrations/2026_10_27_112000_add_scan_archive_details_to_surat_keputusan.php');
        // hari_sampai_terarsip (a later migration) is computed from scan_diunggah_pada: rolled back first, as migrate:rollback would.
        $reportColumns = require database_path('migrations/2026_10_28_180000_add_report_columns_to_surat_keputusan.php');
        $reportColumns->down();
        $migration->down();

        Storage::disk('local')->put('surat-keputusan/lama.pdf', str_repeat('%PDF-1.4 ', 100));
        DB::table('surat_keputusan')->where('nomor_urut', 1)->update(['berkas_scan' => 'surat-keputusan/lama.pdf', 'updated_at' => '2026-03-01 08:00:00']);
        DB::table('surat_keputusan')->where('nomor_urut', 2)->update(['berkas_scan' => 'surat-keputusan/hilang.bin']);

        $migration->up();
        $reportColumns->up();

        $old = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 1)->firstOrFail();
        $this->assertSame(['application/pdf', 900], [$old->berkas_scan_mime, $old->berkas_scan_ukuran]);
        $this->assertSame('2026-03-01 08:00:00', $old->scan_diunggah_pada->format('Y-m-d H:i:s'));
        $this->assertNull($old->scan_diunggah_oleh);

        // Missing file of an unknown type: still archived, without the details that cannot be known.
        $missing = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 2)->firstOrFail();
        $this->assertSame([null, null], [$missing->berkas_scan_mime, $missing->berkas_scan_ukuran]);
        $this->assertNotNull($missing->scan_diunggah_pada);
        $this->assertNull(SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 3)->firstOrFail()->scan_diunggah_pada);
    }
}
