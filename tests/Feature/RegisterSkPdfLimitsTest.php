<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SkReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** The printable register: within what the server can render, and readable across years. */
class RegisterSkPdfLimitsTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private int $lastYear;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->lastYear = now()->year - 1;
    }

    /** A busy year: one SK more than a PDF holds. */
    private function bigYear(): void
    {
        $year = $this->lastYear;
        DB::table('surat_keputusan')->insert(collect(range(1, SkReportService::PDF_MAX_ROWS + 1))->map(fn (int $n) => [
            'tahun' => $year, 'nomor_urut' => $n, 'nomor_sk' => "{$n} TAHUN {$year}", 'tanggal_surat' => "{$year}-03-01",
            'perihal' => 'Panitia kegiatan ' . $n, 'jenis_sk' => 'Keuangan dan Operasional', 'nama_peminta' => 'Waka Kurikulum',
            'created_at' => now(), 'updated_at' => now(),
        ])->all());
    }

    private function exports(): int
    {
        return Activity::inLog('surat_keputusan')->where('event', 'exported')->count();
    }

    public function test_the_limit_is_told_with_the_way_out(): void
    {
        $this->assertNull(SkReportService::pdfRefusal(SkReportService::PDF_MAX_ROWS));
        $this->assertSame(
            'Laporan PDF memuat paling banyak 400 SK, saringan ini berisi 401 SK. Persempit saringan (mis. per bulan atau per jenis SK), atau unduh Excel.',
            SkReportService::pdfRefusal(401),
        );
    }

    public function test_the_api_refuses_a_pdf_too_big_but_still_gives_the_excel(): void
    {
        $this->bigYear();
        Sanctum::actingAs($this->officer, ['*']);

        $this->getJson("/api/penomoran-sk/unduh/pdf?tahun={$this->lastYear}")->assertStatus(422)
            ->assertJsonPath('jumlah', 401)
            ->assertJsonPath('batas_pdf', 400)
            ->assertJsonPath('message', SkReportService::pdfRefusal(401));
        $this->assertSame(0, $this->exports());

        Excel::fake();
        $this->get("/api/penomoran-sk/unduh/excel?tahun={$this->lastYear}")->assertOk();
        $this->assertSame(1, $this->exports());
    }

    public function test_the_panel_warns_instead_of_printing_a_pdf_too_big(): void
    {
        $this->bigYear();
        $this->actingAs($this->officer);

        Livewire::test(ListSuratKeputusan::class)
            ->filterTable('tahun', $this->lastYear)
            ->callAction('pdf')
            ->assertNotified('Laporan PDF terlalu besar')
            ->assertNoFileDownloaded()
            // Narrowed down, it prints.
            ->searchTable('Panitia kegiatan 40')
            ->callAction('pdf')
            ->assertFileDownloaded();

        $this->assertSame(1, $this->exports());
    }

    public function test_a_report_across_years_has_a_heading_per_year(): void
    {
        $year = now()->year;
        SuratKeputusan::create(['tahun' => $this->lastYear, 'nomor_urut' => 9, 'nomor_sk' => SuratKeputusan::composeNumber(9, $this->lastYear), 'tanggal_surat' => "{$this->lastYear}-05-02",
            'perihal' => 'Panitia akhir tahun lalu', 'jenis_sk' => 'Keuangan dan Operasional', 'nama_peminta' => 'Waka Kesiswaan']);
        $sk = SuratKeputusan::query()->orderBy('tahun')->orderBy('nomor_urut')->get();

        $html = view('pdf.surat-keputusan-register', ['sk' => $sk, 'years' => "{$this->lastYear}, {$year}"])->render();
        $this->assertSame(2, substr_count($html, 'class="year"'));
        $this->assertTrue(strpos($html, "Tahun {$this->lastYear} · 1 SK") < strpos($html, "9 TAHUN {$this->lastYear}"));
        $this->assertTrue(strpos($html, "9 TAHUN {$this->lastYear}") < strpos($html, "Tahun {$year} · 6 SK"));

        $single = view('pdf.surat-keputusan-register', ['sk' => $sk->where('tahun', $year)->values(), 'years' => (string) $year])->render();
        $this->assertStringNotContainsString('class="year"', $single);
    }
}
