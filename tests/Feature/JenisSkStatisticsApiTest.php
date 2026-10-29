<?php

namespace Tests\Feature;

use App\Models\PersuratanMaster;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** GET /api/persuratan/jenis-sk/{id}/statistik: one jenis SK over the years, for Laporan & Evaluasi. */
class JenisSkStatisticsApiTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private PersuratanMaster $panitia;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->panitia = PersuratanMaster::where('type', 'jenis_sk')->where('nama', Persuratan::JENIS_SK[2])->firstOrFail();

        $year = now()->year - 1;
        SuratKeputusan::create(['tahun' => $year, 'nomor_urut' => 9, 'nomor_sk' => SuratKeputusan::composeNumber(9, $year), 'tanggal_surat' => "{$year}-05-02",
            'perihal' => 'Panitia akhir tahun lalu', 'jenis_sk' => $this->panitia->nama, 'nama_peminta' => 'Waka Kesiswaan']);
        $this->actingAs($this->officer);
        app(SuratKeputusanService::class)->attachScan(SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 3)->firstOrFail(), UploadedFile::fake()->createWithContent('SK.pdf', '%PDF-1.4'));
    }

    public function test_a_jenis_is_counted_per_year_newest_first(): void
    {
        Sanctum::actingAs($this->officer, ['penomoran-sk:baca']);
        $year = now()->year;

        $this->getJson("/api/persuratan/jenis-sk/{$this->panitia->id}/statistik")->assertOk()
            ->assertJsonPath('nama', $this->panitia->nama)
            ->assertJsonPath('per_tahun', [['tahun' => $year, 'terbit' => 2, 'terarsip' => 1], ['tahun' => $year - 1, 'terbit' => 1, 'terarsip' => 0]])
            ->assertJsonPath('terbit', 3)
            ->assertJsonPath('terarsip', 1)
            ->assertJsonPath('sk_terakhir', "6 TAHUN {$year}");
    }

    public function test_the_statistics_outlive_a_rename(): void
    {
        $this->panitia->update(['nama' => 'Kepanitiaan Kegiatan Madrasah']);
        Sanctum::actingAs($this->officer, ['*']);

        $this->getJson("/api/persuratan/jenis-sk/{$this->panitia->id}/statistik")->assertOk()
            ->assertJsonPath('nama', 'Kepanitiaan Kegiatan Madrasah')
            ->assertJsonPath('terbit', 3);
    }

    public function test_an_unused_jenis_and_who_may_ask(): void
    {
        $new = PersuratanMaster::create(['type' => 'jenis_sk', 'nama' => 'SK Penghargaan', 'is_active' => true, 'sort' => 9]);
        Sanctum::actingAs($this->officer, ['*']);

        $this->getJson("/api/persuratan/jenis-sk/{$new->id}/statistik")->assertOk()
            ->assertJsonPath('per_tahun', [])->assertJsonPath('terbit', 0)->assertJsonPath('sk_terakhir', null);
        $other = PersuratanMaster::where('type', 'tujuan_naskah')->firstOrFail();
        $this->getJson("/api/persuratan/jenis-sk/{$other->id}/statistik")->assertNotFound();

        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail(), ['*']);
        $this->getJson("/api/persuratan/jenis-sk/{$new->id}/statistik")->assertForbidden();
    }
}
