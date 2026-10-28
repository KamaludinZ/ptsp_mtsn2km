<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SkReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** Unduh Laporan SK (PDF): GET /api/penomoran-sk/unduh/pdf, shared with the panel's "Cetak / Unduh PDF". */
class RegisterSkPdfReportApiTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
    }

    public function test_api_clients_download_the_printable_register(): void
    {
        Sanctum::actingAs($this->officer, ['*']);
        $year = now()->year;

        $response = $this->get('/api/penomoran-sk/unduh/pdf?' . http_build_query(['jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan']))->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertMatchesRegularExpression("/^attachment; filename=register-penomoran-sk-{$year}-\\d{8}-\\d{6}\\.pdf$/", $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $response->streamedContent());

        $log = Activity::inLog('surat_keputusan')->where('event', 'exported')->sole();
        $this->assertSame("Mengunduh laporan register SK tahun {$year} (PDF, 2 SK)", $log->description);
        $this->assertSame('api', $log->properties['sumber']);
    }

    public function test_nothing_to_print_is_refused(): void
    {
        Sanctum::actingAs($this->officer, ['*']);

        $this->getJson('/api/penomoran-sk/unduh/pdf?q=tidak ada perihal seperti ini')->assertStatus(422);
        $this->assertSame(0, Activity::inLog('surat_keputusan')->where('event', 'exported')->count());
    }

    public function test_the_printed_register_says_which_criteria_it_holds(): void
    {
        $year = now()->year;
        $criteria = SkReportService::criteria([
            'tahun' => $year, 'jenis_sk' => ['Kepanitiaan dan Tim Pelaksana Kegiatan'], 'status' => 'menunggu_scan',
            'petugas' => $this->officer->id, 'dari' => "{$year}-02-01", 'sampai' => "{$year}-04-30", 'q' => 'panitia',
        ]);

        $this->assertSame([
            'Jenis SK' => 'Kepanitiaan dan Tim Pelaksana Kegiatan',
            'Status' => 'Menunggu scan',
            'Petugas' => $this->officer->name,
            'Tanggal surat' => "1 Feb {$year} – 30 Apr {$year}",
            'Pencarian' => '"panitia"',
        ], $criteria);
        $this->assertSame([], SkReportService::criteria(['tahun' => $year]));
        $this->assertSame(['Tanggal surat' => "sejak 1 Feb {$year}"], SkReportService::criteria(['dari' => "{$year}-02-01"]));

        $html = view('pdf.surat-keputusan-register', ['sk' => SuratKeputusan::orderBy('nomor_urut')->get(), 'years' => (string) $year, 'criteria' => $criteria])->render();
        $this->assertStringContainsString(e('Saringan: Jenis SK: Kepanitiaan dan Tim Pelaksana Kegiatan; Status: Menunggu scan; Petugas: ' . $this->officer->name), $html);
        $this->assertStringNotContainsString('Saringan:', view('pdf.surat-keputusan-register', ['sk' => collect(), 'years' => ''])->render());
    }

    public function test_the_panel_prints_the_same_criteria_as_the_api_reads_them(): void
    {
        $this->actingAs($this->officer);

        $page = Livewire::test(ListSuratKeputusan::class)
            ->filterTable('jenis_sk', ['Kepanitiaan dan Tim Pelaksana Kegiatan'])
            ->filterTable('status', 'menunggu_scan')
            ->searchTable('panitia');

        $this->assertSame(
            ['Jenis SK' => 'Kepanitiaan dan Tim Pelaksana Kegiatan', 'Status' => 'Menunggu scan', 'Pencarian' => '"panitia"'],
            SkReportService::criteria($page->instance()->reportFilters()),
        );
        $this->assertSame(SuratKeputusan::register($page->instance()->reportFilters())->count(), $page->instance()->getAllTableRecordsCount());

        $page->callAction('pdf')->assertFileDownloaded();
        $this->assertSame('panel', Activity::inLog('surat_keputusan')->where('event', 'exported')->sole()->properties['sumber']);
    }
}
