<?php

namespace Tests\Feature;

use App\Exports\SuratKeputusanExport;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/** Cetak / Unduh on the SK register: Excel and PDF of what is on screen. */
class RegisterSkExportButtonsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
    }

    public function test_register_offers_excel_and_pdf(): void
    {
        Livewire::test(ListSuratKeputusan::class)
            ->assertSee('Cetak / Unduh')
            ->assertActionExists('excel')
            ->assertActionHasLabel('excel', 'Unduh Excel')
            ->assertActionExists('pdf')
            ->assertActionHasLabel('pdf', 'Cetak / Unduh PDF');
    }

    public function test_excel_holds_the_filtered_register(): void
    {
        Excel::fake();
        Excel::matchByRegex();
        $year = now()->year;

        Livewire::test(ListSuratKeputusan::class)
            ->filterTable('jenis_sk', ['Kepanitiaan dan Tim Pelaksana Kegiatan'])
            ->callAction('excel');

        Excel::assertDownloaded('/^register-penomoran-sk-' . $year . '-\d{8}-\d{6}\.xlsx$/', function (SuratKeputusanExport $export) use ($year) {
            $rows = $export->query()->get();
            $first = SuratKeputusan::where('nomor_urut', 3)->firstOrFail();

            return $export->title() === 'Register Penomoran SK'
                && $rows->pluck('jenis_sk')->unique()->all() === ['Kepanitiaan dan Tim Pelaksana Kegiatan']
                && $rows->count() === 2
                && $export->headings()[1] === 'Nomor SK'
                && $export->map($first) === [3, "3 TAHUN {$year}", $first->tanggal_surat->format('Y-m-d'), $first->perihal, $first->jenis_sk, $first->nama_peminta, null, 'Menunggu scan', null, $first->pembuat?->name];
        });
    }

    public function test_pdf_lists_the_filtered_register_in_number_order(): void
    {
        $year = now()->year;

        Livewire::test(ListSuratKeputusan::class)
            ->filterTable('jenis_sk', ['Kepanitiaan dan Tim Pelaksana Kegiatan'])
            ->callAction('pdf')
            ->assertFileDownloaded();

        $html = view('pdf.surat-keputusan-register', [
            'sk' => SuratKeputusan::where('jenis_sk', 'Kepanitiaan dan Tim Pelaksana Kegiatan')->orderBy('nomor_urut')->get(),
            'years' => (string) $year,
        ])->render();

        $this->assertStringContainsString("Buku Register Surat Keputusan Tahun {$year}", $html);
        $this->assertTrue(strpos($html, "3 TAHUN {$year}") < strpos($html, "6 TAHUN {$year}"));
        $this->assertStringContainsString('Tentang Panitia penerimaan peserta didik baru', $html);
        $this->assertStringNotContainsString('Penetapan bendahara', $html);
    }

    public function test_each_button_says_what_its_file_holds(): void
    {
        $page = Livewire::test(ListSuratKeputusan::class)->assertSee('Laporan register sesuai filter dan pencarian');

        $this->assertSame('Semua kolom, urut seperti di layar; untuk diolah lagi di spreadsheet.', $page->instance()->getAction('excel')->getTooltip());
        $this->assertSame('A4 mendatar berkop madrasah, urut nomor SK, paling banyak 400 SK; siap dicetak.', $page->instance()->getAction('pdf')->getTooltip());
    }

    public function test_the_download_is_announced_and_named_after_the_period(): void
    {
        Excel::fake();
        $this->travelTo(now()->setTime(9, 30));
        $year = now()->year;
        $stamp = now()->format('Ymd-His');

        Livewire::test(ListSuratKeputusan::class)
            ->filterTable('jenis_sk', ['Kepanitiaan dan Tim Pelaksana Kegiatan'])
            ->callAction('excel')
            ->assertNotified('Mengunduh 2 SK')
            ->resetTableFilters()
            ->removeTableFilter('tahun')
            ->callAction('pdf')
            ->assertNotified('Mengunduh 6 SK')
            ->assertFileDownloaded("register-penomoran-sk-semua-tahun-{$stamp}.pdf");
        Excel::assertDownloaded("register-penomoran-sk-{$year}-{$stamp}.xlsx");
    }

    public function test_nothing_is_downloaded_when_no_sk_matches(): void
    {
        Livewire::test(ListSuratKeputusan::class)
            ->searchTable('tidak ada perihal seperti ini')
            ->callAction('pdf')
            ->assertNotified('Tidak ada SK untuk diunduh')
            ->assertNoFileDownloaded()
            ->callAction('excel')
            ->assertNoFileDownloaded();
    }

    public function test_empty_filter_still_gives_a_readable_pdf(): void
    {
        $html = view('pdf.surat-keputusan-register', ['sk' => collect(), 'years' => ''])->render();

        $this->assertStringContainsString('Tidak ada SK pada filter ini.', $html);
    }
}
