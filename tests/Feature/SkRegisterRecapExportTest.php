<?php

namespace Tests\Feature;

use App\Exports\SkRegisterRecapExport;
use App\Filament\Pages\Reports\SkRegisterRecap;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Support\SkRegisterRecap as Recap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** Rekap Register SK as a file (Excel, PDF), and each month down to its SK in the register. */
class SkRegisterRecapExportTest extends TestCase
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
        $this->actingAs($this->officer);
    }

    public function test_the_excel_workbook_holds_the_recap_sheets(): void
    {
        Excel::fake();
        $year = now()->year;

        Livewire::test(SkRegisterRecap::class)
            ->assertSee('Unduh rekap')
            ->callAction('excel');

        Excel::assertDownloaded("rekap-register-sk-{$year}.xlsx", function (SkRegisterRecapExport $export) use ($year) {
            $sheets = collect($export->sheets());
            $months = $sheets[1]->array();

            return $sheets->map->title()->all() === ['Ringkasan', 'Per Bulan', 'Per Jenis SK', 'Per Petugas']
                && $sheets[0]->array()[1] === ['Tahun', $year]
                && $sheets[0]->array()[4] === ['Nomor SK terbit', 6]
                && $months[1] === ['Januari', 2, 0, 2]
                && end($months) === ['Total', 6, 0, 6]
                && count($sheets[2]->array()) === 6;
        });

        $log = Activity::inLog('surat_keputusan')->where('event', 'exported')->sole();
        $this->assertSame("Mengunduh rekap register SK tahun {$year} (Excel)", $log->description);
        $this->assertSame(['Excel', $year, 6], [$log->properties['format'], $log->properties['tahun'], $log->properties['jumlah']]);
    }

    public function test_the_pdf_is_a_printable_recap(): void
    {
        $year = now()->year;

        Livewire::test(SkRegisterRecap::class)->callAction('pdf')->assertFileDownloaded("rekap-register-sk-{$year}.pdf");

        $html = view('pdf.sk-register-recap', ['recap' => Recap::build($year)])->render();
        $this->assertStringContainsString("Rekap Register Surat Keputusan Tahun {$year}", $html);
        $this->assertStringContainsString('oleh ' . e($this->officer->name), $html);
        $this->assertStringContainsString('Kepanitiaan dan Tim Pelaksana Kegiatan', $html);
        $this->assertSame(1, Activity::inLog('surat_keputusan')->where('event', 'exported')->where('properties->format', 'PDF')->count());
    }

    public function test_a_month_leads_to_its_sk_in_the_register(): void
    {
        $page = Livewire::test(SkRegisterRecap::class);
        $url = $page->instance()->monthUrl(1);
        $page->assertSee(e($url), false);

        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        Livewire::withQueryParams($query)->test(ListSuratKeputusan::class)
            ->assertCanSeeTableRecords(SuratKeputusan::where('tahun', now()->year)->whereIn('nomor_urut', [1, 2])->get())
            ->assertCountTableRecords(2);
    }
}
