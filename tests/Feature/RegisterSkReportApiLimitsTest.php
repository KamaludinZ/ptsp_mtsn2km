<?php

namespace Tests\Feature;

use App\Exports\SkRegisterRecapExport;
use App\Filament\Pages\Reports\SkRegisterRecap;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\User;
use App\Services\SkReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** Rekap Register SK for API clients, and one download limit for every SK report, panel and API. */
class RegisterSkReportApiLimitsTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Excel::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
    }

    public function test_api_clients_read_the_year_recap(): void
    {
        Sanctum::actingAs($this->officer, ['*']);
        $year = now()->year;

        $this->getJson('/api/penomoran-sk/rekap')->assertOk()
            ->assertJsonPath('tahun', $year)
            ->assertJsonPath('terbit', 6)
            ->assertJsonPath('menunggu_scan', 6)
            ->assertJsonPath('batas_hari_scan', 14)
            ->assertJsonCount(12, 'per_bulan')
            ->assertJsonPath('per_bulan.0', ['bulan' => 1, 'nama' => 'Januari', 'terbit' => 2, 'terarsip' => 0]);
        $this->getJson('/api/penomoran-sk/rekap?tahun=' . ($year - 1))->assertOk()->assertJsonPath('terbit', 0);
        $this->getJson('/api/penomoran-sk/rekap?tahun=dulu')->assertJsonValidationErrors(['tahun' => 'Tahun harus berupa tahun antara 2000 dan 2100.']);

        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail(), ['*']);
        $this->getJson('/api/penomoran-sk/rekap')->assertForbidden();
    }

    public function test_api_clients_download_the_recap_and_it_is_logged(): void
    {
        Sanctum::actingAs($this->officer, ['*']);
        $year = now()->year;

        $this->get('/api/penomoran-sk/rekap/unduh/excel')->assertOk();
        Excel::assertDownloaded("rekap-register-sk-{$year}.xlsx", fn (SkRegisterRecapExport $export) => count($export->sheets()) === 4);
        $this->get('/api/penomoran-sk/rekap/unduh/pdf')->assertOk()->assertHeader('content-type', 'application/pdf');

        $logs = Activity::inLog('surat_keputusan')->where('event', 'exported')->orderBy('id')->get();
        $this->assertSame(["Mengunduh rekap register SK tahun {$year} (Excel)", "Mengunduh rekap register SK tahun {$year} (PDF)"], $logs->pluck('description')->all());
        $this->assertSame(['api', 'rekap'], [$logs[0]->properties['sumber'], $logs[0]->properties['laporan']]);
    }

    public function test_one_download_limit_for_every_report_of_an_account(): void
    {
        // Panel first (Sanctum::actingAs switches the default guard): register and recap count together.
        $this->actingAs($this->officer);
        foreach (range(1, SkReportService::DOWNLOADS_PER_MINUTE / 2) as $n) {
            Livewire::test(ListSuratKeputusan::class)->callAction('excel')->assertNotNotified('Terlalu banyak unduhan');
            Livewire::test(SkRegisterRecap::class)->callAction('excel')->assertNotNotified('Terlalu banyak unduhan');
        }
        Livewire::test(SkRegisterRecap::class)->callAction('pdf')->assertNotified('Terlalu banyak unduhan')->assertNoFileDownloaded();
        $this->assertSame(SkReportService::DOWNLOADS_PER_MINUTE, Activity::inLog('surat_keputusan')->where('event', 'exported')->count());

        // The API counts against the same limit.
        Sanctum::actingAs($this->officer, ['*']);
        $response = $this->getJson('/api/penomoran-sk/unduh/excel')->assertStatus(429);
        $this->assertStringStartsWith('Batas 6 unduhan laporan per menit tercapai.', $response->json('message'));
        $this->assertSame((string) $response->json('coba_lagi_dalam_detik'), $response->headers->get('Retry-After'));
        $this->getJson('/api/penomoran-sk/rekap/unduh/pdf')->assertStatus(429);

        // Another officer is not held back, and a minute later this one may go on.
        Sanctum::actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail(), ['*']);
        $this->get('/api/penomoran-sk/unduh/excel')->assertOk();
        $this->travel(61)->seconds();
        Sanctum::actingAs($this->officer, ['*']);
        $this->get('/api/penomoran-sk/rekap/unduh/excel')->assertOk();
    }
}
