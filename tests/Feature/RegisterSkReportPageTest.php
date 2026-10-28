<?php

namespace Tests\Feature;

use App\Exports\SuratKeputusanExport;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/** Unduh Laporan SK: the register page tells which period and how many SK the report holds. */
class RegisterSkReportPageTest extends TestCase
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

    public function test_the_register_shows_the_period_and_size_of_the_report(): void
    {
        Livewire::test(ListSuratKeputusan::class)
            ->assertSee('Register tahun ' . now()->year)
            ->assertSee('6 SK sesuai filter dan pencarian.')
            ->assertSee('Laporan register sesuai filter dan pencarian');
    }

    public function test_the_scope_follows_filters_and_search(): void
    {
        Livewire::test(ListSuratKeputusan::class)
            ->filterTable('jenis_sk', ['Kepanitiaan dan Tim Pelaksana Kegiatan'])
            ->assertSee('2 SK sesuai filter dan pencarian.')
            ->resetTableFilters()
            ->searchTable('tidak ada perihal seperti ini')
            ->assertSee('Tidak ada SK pada filter dan pencarian ini; laporan yang diunduh akan kosong.');

        Livewire::test(ListSuratKeputusan::class)
            ->removeTableFilter('tahun')
            ->assertSee('Register semua tahun');
    }

    public function test_the_excel_holds_as_many_sk_as_the_page_says(): void
    {
        Excel::fake();
        Excel::matchByRegex();

        $page = Livewire::test(ListSuratKeputusan::class)->filterTable('status', 'menunggu_scan');
        $count = $page->instance()->getAllTableRecordsCount();
        $page->assertSee($count . ' SK sesuai filter dan pencarian.')->callAction('excel');

        Excel::assertDownloaded('/^register-penomoran-sk-.+\.xlsx$/', fn (SuratKeputusanExport $export) => $export->query()->count() === $count);
    }
}
