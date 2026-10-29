<?php

namespace Tests\Feature;

use App\Exports\SuratKeputusanExport;
use App\Models\SuratKeputusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** Unduh Laporan SK (Excel): GET /api/penomoran-sk/unduh/excel and the workbook itself. */
class RegisterSkExcelReportApiTest extends TestCase
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

    private function exports()
    {
        return Activity::inLog('surat_keputusan')->where('event', 'exported')->get();
    }

    public function test_api_clients_download_the_register_of_the_list_filters(): void
    {
        Excel::fake();
        Excel::matchByRegex();
        Sanctum::actingAs($this->officer, ['*']);
        $year = now()->year;

        $this->get('/api/penomoran-sk/unduh/excel?' . http_build_query(['jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan', 'arah' => 'asc']))->assertOk();

        Excel::assertDownloaded("/^register-penomoran-sk-{$year}-\\d{8}-\\d{6}\\.xlsx$/", fn (SuratKeputusanExport $export) => $export->query()->pluck('nomor_urut')->all() === [3, 6]);

        $log = $this->exports()->sole();
        $this->assertSame("Mengunduh laporan register SK tahun {$year} (Excel, 2 SK)", $log->description);
        $this->assertSame(['api', ['Kepanitiaan dan Tim Pelaksana Kegiatan']], [$log->properties['sumber'], $log->properties['saringan']['jenis_sk']]);
        $this->assertTrue($log->causer->is($this->officer));
    }

    public function test_every_year_and_a_search_are_named_in_the_file_and_the_log(): void
    {
        Excel::fake();
        Excel::matchByRegex();
        Sanctum::actingAs($this->officer, ['*']);

        $this->get('/api/penomoran-sk/unduh/excel?tahun=semua&q=panitia')->assertOk();

        Excel::assertDownloaded('/^register-penomoran-sk-semua-tahun-\d{8}-\d{6}\.xlsx$/');
        $log = $this->exports()->sole();
        $this->assertSame('Mengunduh laporan register SK semua tahun (Excel, 2 SK)', $log->description);
        $this->assertSame('panitia', $log->properties['pencarian']);
    }

    public function test_nothing_to_download_is_refused_and_not_logged(): void
    {
        Sanctum::actingAs($this->officer, ['*']);

        $this->getJson('/api/penomoran-sk/unduh/excel?q=tidak ada perihal seperti ini')->assertStatus(422)
            ->assertJsonPath('message', 'Tidak ada SK untuk diunduh: Register tahun ' . now()->year . ' kosong dengan pencarian dan saringan saat ini.');
        $this->getJson('/api/penomoran-sk/unduh/excel?sampai=2020-01-01&dari=2021-01-01')->assertJsonValidationErrors('sampai');

        $this->assertCount(0, $this->exports());
    }

    public function test_only_the_back_office_downloads_and_only_known_formats(): void
    {
        $this->getJson('/api/penomoran-sk/unduh/excel')->assertUnauthorized();

        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail(), ['*']);
        $this->getJson('/api/penomoran-sk/unduh/excel')->assertForbidden();

        Sanctum::actingAs($this->officer, ['*']);
        $this->getJson('/api/penomoran-sk/unduh/docx')->assertNotFound();
    }

    public function test_the_workbook_keeps_its_headings_in_view_and_filterable(): void
    {
        $base = tempnam(sys_get_temp_dir(), 'sk');
        $path = $base . '.xlsx';
        file_put_contents($path, Excel::raw(new SuratKeputusanExport(SuratKeputusan::query()->orderBy('nomor_urut')), ExcelFormat::XLSX));
        // Maatwebsite leaves the export's value binder in place; read the file back as written.
        \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(new \PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder);
        $sheet = IOFactory::load($path)->getActiveSheet();
        unlink($path);
        unlink($base);

        $this->assertSame('Register Penomoran SK', $sheet->getTitle());
        $this->assertSame('C2', $sheet->getFreezePane());
        $this->assertSame('A1:J1', $sheet->getAutoFilter()->getRange());
        $this->assertSame('Nomor SK', $sheet->getCell('B1')->getValue());
        $this->assertSame('1 TAHUN ' . now()->year, $sheet->getCell('B2')->getValue());
    }
}
