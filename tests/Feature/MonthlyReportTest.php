<?php

namespace Tests\Feature;

use App\Filament\Pages\Reports\MonthlyReport;
use App\Models\Ticket;
use App\Models\User;
use App\Support\MonthlyReport as Report;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

/** Laporan bulanan / kinerja layanan. */
class MonthlyReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RoleAccess::sync();
        $this->travelTo(Carbon::parse('2026-10-15 10:00'));
    }

    private function leader(): User
    {
        return User::factory()->create(['user_type' => 'pegawai'])->assignRole('kepala_sekolah');
    }

    public function test_report_counts_one_month_and_compares_with_the_previous(): void
    {
        Ticket::factory()->count(3)->create(['status' => 'submitted', 'created_at' => '2026-09-10 09:00']);
        Ticket::factory()->count(2)->create(['status' => 'submitted', 'created_at' => '2026-10-02 09:00']);
        Ticket::factory()->create(['status' => 'submitted', 'created_at' => '2026-10-14 09:00']);

        $october = Report::build(Report::month('2026-10'));
        $this->assertSame(3, $october['tickets']['total']);
        $this->assertSame(['value' => 0.0, 'label' => 'Sama dengan bulan lalu', 'trend' => 'flat', 'good' => null], $october['changes']['total']);
        $this->assertCount(15, $october['daily']);
        $this->assertSame(2, $october['daily'][1]['total']);

        $september = Report::build(Report::month('2026-09'));
        $this->assertSame(3, $september['tickets']['total']);
        $this->assertCount(30, $september['daily']);
        $this->assertSame('September 2026', $september['label']);
    }

    public function test_month_input_is_sanitised(): void
    {
        $this->assertSame('2026-10', Report::month(null)->format('Y-m'));
        $this->assertSame('2026-10', Report::month('2027-01')->format('Y-m'));
        $this->assertSame('2026-10', Report::month('2026-13')->format('Y-m'));
        $this->assertSame('2025-11', Report::month('2025-11')->format('Y-m'));
        $this->assertSame(['2026-10', 'Oktober 2026'], [array_key_first(Report::monthOptions()), Report::monthOptions()['2026-10']]);
        $this->assertCount(Report::MONTHS_BACK, Report::monthOptions());
    }

    public function test_changes_are_labelled_with_direction_and_meaning(): void
    {
        $this->assertSame(['value' => 50.0, 'label' => '+50% dari bulan lalu', 'trend' => 'up', 'good' => true], Report::change(6, 4));
        $this->assertSame('down', Report::change(2.5, 4.0, lowerIsBetter: true)['trend']);
        $this->assertTrue(Report::change(2.5, 4.0, lowerIsBetter: true)['good']);
        $this->assertSame('-10 poin dari bulan lalu', Report::change(80, 90, points: true)['label']);
        $this->assertNull(Report::change(null, 3));
    }

    public function test_leaders_open_the_page_and_switch_months(): void
    {
        Ticket::factory()->create(['status' => 'submitted', 'created_at' => '2026-09-10 09:00']);
        $this->actingAs($this->leader());

        $this->get(MonthlyReport::getUrl())->assertOk()->assertSee('Laporan Bulanan')->assertSee('Oktober 2026');

        Livewire::test(MonthlyReport::class)
            ->assertSet('month', '2026-10')
            ->assertSee('Belum ada permohonan bulan ini')
            ->set('month', '2026-09')
            ->assertSee('Permohonan masuk per hari')
            ->assertDontSee('Belum ada permohonan bulan ini')
            ->set('month', '2030-01')
            ->assertSet('month', '2026-10');
    }

    public function test_applicants_cannot_open_the_report(): void
    {
        $this->actingAs(User::factory()->create());

        // Applicants are sent to their own panel (RedirectToOwnPanel) instead of a 403.
        $this->get(MonthlyReport::getUrl())->assertRedirect('/portal');
    }

    public function test_month_and_year_picker_in_indonesian(): void
    {
        Ticket::factory()->create(['status' => 'submitted', 'created_at' => '2024-05-10 09:00']);
        $this->actingAs($this->leader());

        $this->assertSame([2026, 2025, 2024], Report::years());
        $this->assertSame('Mei 2024', Report::label(Carbon::parse('2024-05-01')));

        Livewire::test(MonthlyReport::class)
            ->assertSee(['Januari', 'Februari', 'Desember', 'Bulan sebelumnya'])
            ->assertSee('<option value="11" disabled', false)
            ->set('monthNumber', 3)
            ->assertSet('month', '2026-03')
            ->set('year', 2025)
            ->assertSet('month', '2025-03')
            ->set('monthNumber', 12)
            ->set('year', 2026)
            ->assertSet('month', '2026-10')
            ->call('previousMonth')
            ->assertSet('month', '2026-09')
            ->assertSet('monthNumber', 9)
            ->assertSee('Bulan ini')
            ->call('thisMonth')
            ->assertSet('month', '2026-10')
            ->call('nextMonth')
            ->assertSet('month', '2026-10');
    }

    public function test_month_can_be_opened_from_the_url(): void
    {
        $this->actingAs($this->leader());

        Livewire::withQueryParams(['bulan' => '2026-02'])->test(MonthlyReport::class)
            ->assertSet('monthNumber', 2)
            ->assertSet('year', 2026)
            ->assertSee('1 Februari – 28 Februari 2026');
    }

    public function test_service_recap_has_one_row_per_service_and_a_total(): void
    {
        $legalisir = \App\Models\Service::factory()->create(['name' => 'Legalisir']);
        $mutasi = \App\Models\Service::factory()->create(['name' => 'Mutasi']);
        $make = fn (array $attributes) => Ticket::factory()->create($attributes + ['created_at' => '2026-10-01 08:00']);
        $make(['service_id' => $legalisir->id, 'mode' => 'online', 'status' => 'completed', 'estimated_completion_date' => '2026-10-05', 'actual_completion_date' => '2026-10-03']);
        $make(['service_id' => $legalisir->id, 'mode' => 'offline', 'status' => 'completed', 'estimated_completion_date' => '2026-10-02', 'actual_completion_date' => '2026-10-05']);
        $make(['service_id' => $legalisir->id, 'mode' => 'online', 'status' => 'in_process']);
        $make(['service_id' => $mutasi->id, 'mode' => 'offline', 'status' => 'rejected']);
        Ticket::factory()->create(['service_id' => $mutasi->id, 'created_at' => '2026-09-20 08:00']);

        $recap = Report::services(Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31 23:59:59'));

        $this->assertSame(['Legalisir', 'Mutasi'], array_column($recap['rows'], 'name'));
        $this->assertSame(
            ['name' => 'Legalisir', 'total' => 3, 'online' => 2, 'offline' => 1, 'completed' => 2, 'rejected' => 0, 'open' => 1, 'on_time_rate' => 50, 'avg_days' => 3.0],
            $recap['rows'][0],
        );
        $this->assertSame(
            ['name' => 'Total', 'total' => 4, 'online' => 2, 'offline' => 2, 'completed' => 2, 'rejected' => 1, 'open' => 1, 'on_time_rate' => 50, 'avg_days' => 3.0],
            $recap['total'],
        );

        $this->actingAs($this->leader());
        Livewire::test(MonthlyReport::class)
            ->assertSee('Rekap jenis layanan')
            ->assertSeeInOrder(['Legalisir', 'Mutasi', 'Total'])
            ->assertSee('3 hari');
    }

    public function test_empty_month_explains_itself_and_links_to_the_latest_month_with_data(): void
    {
        Ticket::factory()->create(['status' => 'submitted', 'created_at' => '2026-06-10 09:00']);
        $this->actingAs($this->leader());

        $this->assertSame('2026-06', Report::build(Report::month('2026-08'))['nearest_with_data']->format('Y-m'));
        $this->assertNull(Report::build(Report::month('2026-06'))['nearest_with_data']);

        Livewire::test(MonthlyReport::class)
            ->set('month', '2026-08')
            ->assertSee('Tidak ada permohonan pada Agustus 2026')
            ->assertDontSee('Rekap jenis layanan')
            ->assertDontSee('Permohonan masuk per hari')
            ->assertSee('Kepuasan masyarakat (IKM)')
            ->assertSee('Lihat Juni 2026')
            ->call('openMonth', '2026-06')
            ->assertSet('month', '2026-06')
            ->assertSee('Rekap jenis layanan');

        Livewire::test(MonthlyReport::class)->set('month', '2026-05')->assertDontSee('Lihat ');
    }

    public function test_print_view_has_the_letterhead_recap_and_signature(): void
    {
        \App\Models\AppSetting::set('app_name_full', 'MTsN 2 Kota Malang');
        \App\Models\AppSetting::set('contact_address', 'Jl. Contoh No. 1, Malang');
        $legalisir = \App\Models\Service::factory()->create(['name' => 'Legalisir']);
        Ticket::factory()->create(['service_id' => $legalisir->id, 'status' => 'submitted', 'created_at' => '2026-09-03 09:00']);
        $leader = $this->leader();
        $this->actingAs($leader);

        $this->get(route('reports.monthly.print', ['bulan' => '2026-09']))
            ->assertOk()
            ->assertSeeInOrder(['Kementerian Agama Republik Indonesia', 'MTsN 2 Kota Malang', 'Jl. Contoh No. 1, Malang', 'Laporan Bulanan Kinerja Layanan PTSP', 'Periode 1 September – 30 September 2026'])
            ->assertSeeInOrder(['Rekap Jenis Layanan', 'Legalisir', 'Total'])
            ->assertSeeInOrder(['Kepala Madrasah', $leader->name]);

        $this->get(route('reports.monthly.print', ['bulan' => '2026-08']))->assertOk()->assertSee('Tidak ada permohonan pada Agustus 2026');

        Livewire::test(MonthlyReport::class)->set('month', '2026-09')
            ->assertActionHasUrl('print', route('reports.monthly.print', ['bulan' => '2026-09']));
    }

    public function test_print_view_is_for_report_readers_only(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('reports.monthly.print'))->assertForbidden();

        auth()->logout();
        $this->get(route('reports.monthly.print'))->assertRedirect();
    }

    public function test_report_downloads_as_pdf_and_excel(): void
    {
        $legalisir = \App\Models\Service::factory()->create(['name' => 'Legalisir']);
        Ticket::factory()->count(2)->create(['service_id' => $legalisir->id, 'status' => 'submitted', 'created_at' => '2026-09-03 09:00']);
        $this->actingAs($this->leader());

        $pdf = $this->get(route('reports.monthly.download', ['format' => 'pdf', 'bulan' => '2026-09']))->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringContainsString('laporan-bulanan-2026-09.pdf', $pdf->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        \Maatwebsite\Excel\Facades\Excel::fake();
        $this->get(route('reports.monthly.download', ['format' => 'xlsx', 'bulan' => '2026-09']))->assertOk();
        \Maatwebsite\Excel\Facades\Excel::assertDownloaded('laporan-bulanan-2026-09.xlsx', function (\App\Exports\MonthlyReportExport $export) {
            [$summary, $recap] = $export->sheets();
            $this->assertSame(['Permohonan masuk', 2], $summary->array()[4]);
            $legalisir = $recap->array()[1];
            $this->assertSame([1, 'Legalisir', 2], array_slice($legalisir, 0, 3));
            $this->assertSame(2, $legalisir[7]); // dalam proses
            $this->assertSame('Total', $recap->array()[2][1]);

            return true;
        });

        $this->get(route('reports.monthly.download', ['format' => 'docx']))->assertNotFound();
        Livewire::test(MonthlyReport::class)->set('month', '2026-09')
            ->assertActionHasUrl('downloadPdf', route('reports.monthly.download', ['format' => 'pdf', 'bulan' => '2026-09']));
    }

    public function test_downloads_are_for_report_readers_only(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('reports.monthly.download', ['format' => 'xlsx']))->assertForbidden();
    }

    public function test_year_recap_aggregates_every_month_in_one_query(): void
    {
        $make = fn (array $attributes) => Ticket::factory()->create($attributes + ['mode' => 'online']);
        $make(['status' => 'completed', 'created_at' => '2026-01-10 08:00', 'estimated_completion_date' => '2026-01-15', 'actual_completion_date' => '2026-01-12']);
        $make(['status' => 'completed', 'created_at' => '2026-01-20 08:00', 'estimated_completion_date' => '2026-01-21', 'actual_completion_date' => '2026-01-25']);
        $make(['status' => 'rejected', 'created_at' => '2026-03-02 08:00', 'mode' => 'offline']);
        $make(['status' => 'submitted', 'created_at' => '2025-12-31 23:00']);

        \Illuminate\Support\Facades\DB::enableQueryLog();
        $year = Report::year(2026);
        $this->assertCount(1, \Illuminate\Support\Facades\DB::getQueryLog());

        $this->assertCount(12, $year['months']);
        $this->assertSame(['Januari', 2, 2, 50, 3.5], [$year['months'][0]['label'], $year['months'][0]['total'], $year['months'][0]['completed'], $year['months'][0]['on_time_rate'], $year['months'][0]['avg_days']]);
        $this->assertSame([0, null, null], [$year['months'][1]['total'], $year['months'][1]['on_time_rate'], $year['months'][1]['avg_days']]);
        $this->assertSame([1, 1, 1], [$year['months'][2]['total'], $year['months'][2]['rejected'], $year['months'][2]['offline']]);
        $this->assertTrue($year['months'][11]['future']);
        $this->assertFalse($year['months'][9]['future']);
        $this->assertSame([3, 2, 1], [$year['total']['total'], $year['total']['completed'], $year['total']['rejected']]);
    }

    public function test_summary_api_matches_the_report(): void
    {
        Ticket::factory()->count(2)->create(['status' => 'submitted', 'created_at' => '2026-09-10 09:00']);
        Ticket::factory()->create(['status' => 'submitted', 'created_at' => '2026-10-02 09:00']);
        \Laravel\Sanctum\Sanctum::actingAs($this->leader());

        $this->getJson('/api/layanan/laporan-bulanan?bulan=2026-10')->assertOk()
            ->assertJsonPath('label', 'Oktober 2026')
            ->assertJsonPath('bulan_berjalan', true)
            ->assertJsonPath('periode.dari', '2026-10-01')
            ->assertJsonPath('periode.sampai', '2026-10-15')
            ->assertJsonPath('permohonan', 1)
            ->assertJsonPath('dibanding_bulan_lalu.bulan', 'September 2026')
            ->assertJsonPath('dibanding_bulan_lalu.permohonan.label', '-50% dari bulan lalu')
            ->assertJsonPath('dibanding_bulan_lalu.permohonan.baik', false)
            ->assertJsonCount(15, 'harian')
            ->assertJsonPath('harian.1', ['tanggal' => '2026-10-02', 'jumlah' => 1])
            ->assertJsonPath('unduhan.xlsx', route('api.layanan.laporan-bulanan.ekspor', ['format' => 'xlsx', 'bulan' => '2026-10']));

        $this->getJson('/api/layanan/laporan-bulanan?bulan=2026-08')->assertOk()
            ->assertJsonPath('permohonan', 0)
            ->assertJsonPath('bulan_terakhir_berisi_data', null);
        $this->getJson('/api/layanan/laporan-bulanan?bulan=2026-11')->assertUnprocessable();
    }

    public function test_service_and_year_recap_apis(): void
    {
        $legalisir = \App\Models\Service::factory()->create(['name' => 'Legalisir']);
        $mutasi = \App\Models\Service::factory()->create(['name' => 'Mutasi']);
        Ticket::factory()->count(2)->create(['service_id' => $legalisir->id, 'status' => 'submitted', 'mode' => 'online', 'created_at' => '2026-09-03 09:00']);
        Ticket::factory()->create(['service_id' => $mutasi->id, 'status' => 'rejected', 'mode' => 'offline', 'created_at' => '2026-09-04 09:00']);
        \Laravel\Sanctum\Sanctum::actingAs($this->leader());

        $this->getJson('/api/layanan/laporan-bulanan/rekap-layanan?bulan=2026-09')->assertOk()
            ->assertJsonPath('label', 'September 2026')
            ->assertJsonPath('data.0', ['nama' => 'Legalisir', 'jumlah' => 2, 'online' => 2, 'loket' => 0, 'selesai' => 0, 'ditolak_batal' => 0, 'dalam_proses' => 2, 'persen_tepat_waktu' => null, 'rata_rata_hari' => null])
            ->assertJsonPath('data.1.nama', 'Mutasi')
            ->assertJsonPath('total.jumlah', 3)
            ->assertJsonPath('total.ditolak_batal', 1);
        $this->getJson('/api/layanan/laporan-bulanan/rekap-layanan?bulan=2026-08')->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('total.jumlah', 0);

        $this->getJson('/api/layanan/rekap-tahunan?tahun=2026')->assertOk()
            ->assertJsonCount(12, 'data')
            ->assertJsonPath('data.8.bulan', '2026-09')
            ->assertJsonPath('data.8.nama', 'September')
            ->assertJsonPath('data.8.jumlah', 3)
            ->assertJsonPath('data.11.belum_berjalan', true)
            ->assertJsonPath('total.jumlah', 3);
        $this->getJson('/api/layanan/rekap-tahunan?tahun=2030')->assertUnprocessable();

        \Laravel\Sanctum\Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/layanan/laporan-bulanan/rekap-layanan')->assertForbidden();
    }

    public function test_pdf_export_through_the_api_has_page_numbers_and_is_audited(): void
    {
        Ticket::factory()->create(['status' => 'submitted', 'created_at' => '2026-09-03 09:00']);
        $leader = $this->leader();
        \Laravel\Sanctum\Sanctum::actingAs($leader);

        $response = $this->get('/api/layanan/laporan-bulanan/ekspor/pdf?bulan=2026-09')->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('filename="laporan-bulanan-2026-09.pdf"', $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $this->assertDatabaseHas('activity_log', ['causer_id' => $leader->id, 'description' => 'Mengunduh laporan bulanan September 2026 (PDF)']);

        $this->getJson('/api/layanan/laporan-bulanan/ekspor/pdf?bulan=2026-12')->assertUnprocessable();
        $this->getJson('/api/layanan/laporan-bulanan/ekspor/docx')->assertNotFound();

        \Laravel\Sanctum\Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/layanan/laporan-bulanan/ekspor/pdf')->assertForbidden();
    }

    public function test_excel_export_through_the_api_has_summary_recap_and_daily_sheets(): void
    {
        Ticket::factory()->count(2)->create(['status' => 'submitted', 'mode' => 'offline', 'created_at' => '2026-09-03 09:00']);
        \Laravel\Sanctum\Sanctum::actingAs($this->leader());

        $response = $this->get('/api/layanan/laporan-bulanan/ekspor/xlsx?bulan=2026-09')->assertOk();
        $this->assertStringContainsString('laporan-bulanan-2026-09.xlsx', $response->headers->get('content-disposition'));

        $file = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($file, $response->streamedContent() ?: $response->getFile()->getContent());
        $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        unlink($file);

        $this->assertSame(['Ringkasan', 'Rekap Jenis Layanan', 'Harian'], $book->getSheetNames());
        $this->assertSame('Permohonan masuk', $book->getSheet(0)->getCell('A5')->getValue());
        $this->assertSame(2, $book->getSheet(0)->getCell('B5')->getValue());
        $this->assertSame(0, $book->getSheet(0)->getCell('B6')->getValue()); // zeros are kept, not blank
        $this->assertSame('Status', $book->getSheet(0)->getCell('A22')->getValue());
        $this->assertTrue($book->getSheet(0)->getStyle('A22')->getFont()->getBold());
        $daily = $book->getSheetByName('Harian');
        $this->assertSame(['2026-09-03', 2], [$daily->getCell('A4')->getValue(), $daily->getCell('B4')->getValue()]);
        $this->assertSame(['Total', 2], [$daily->getCell('A32')->getValue(), $daily->getCell('B32')->getValue()]);
        $this->assertSame('A2', $daily->getFreezePane());
    }
}
