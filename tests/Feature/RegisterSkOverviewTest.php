<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Filament\Resources\SuratKeputusanResource\Widgets\SkYearOverview;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** The Minta Nomor SK page: the register with this year's numbers at a glance. */
class RegisterSkOverviewTest extends TestCase
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

    public function test_summary_counts_this_years_numbers_and_the_next_one(): void
    {
        $year = now()->year;
        SuratKeputusan::where('nomor_urut', 2)->update(['berkas_scan' => 'surat-keputusan/2.pdf', 'scan_diunggah_pada' => now()]);

        $this->assertSame([
            'tahun' => $year,
            'terbit' => 6,
            'terakhir' => "6 TAHUN {$year}",
            'berikutnya' => "7 TAHUN {$year}",
            'menunggu_scan' => 5,
        ], SuratKeputusanService::yearSummary($year));

        $this->assertSame(['tahun' => $year + 1, 'terbit' => 0, 'terakhir' => null, 'berikutnya' => '1 TAHUN ' . ($year + 1), 'menunggu_scan' => 0], SuratKeputusanService::yearSummary($year + 1));
    }

    public function test_next_number_follows_the_highest_and_stops_at_9999(): void
    {
        $year = now()->year;
        SuratKeputusan::withDeletesAllowed(fn () => SuratKeputusan::where('nomor_urut', 3)->delete());

        $this->assertSame(7, SuratKeputusanService::nextNumber($year));

        SuratKeputusan::create(['tahun' => $year, 'nomor_urut' => 9999, 'nomor_sk' => "9999 TAHUN {$year}", 'tanggal_surat' => now(), 'perihal' => 'Terakhir', 'jenis_sk' => 'Keuangan dan Operasional', 'nama_peminta' => 'Uji']);
        $this->assertNull(SuratKeputusanService::nextNumber($year));
        $this->assertNull(SuratKeputusanService::yearSummary($year)['berikutnya']);
    }

    public function test_page_shows_the_summary_above_the_register(): void
    {
        $year = now()->year;

        $this->get(SuratKeputusanResource::getUrl('index'))->assertOk()
            ->assertSeeInOrder(["Nomor SK terbit {$year}", 'Nomor terakhir', "6 TAHUN {$year}", 'Nomor berikutnya', "7 TAHUN {$year}", 'Menunggu scan', 'Pengangkatan wali kelas']);

        Livewire::test(SkYearOverview::class)->assertSee("7 TAHUN {$year}")->assertSee('Diberikan pada permintaan berikutnya');
    }

    public function test_taking_a_number_moves_the_summary_on(): void
    {
        $year = now()->year;

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', ['tanggal_surat' => now()->toDateString(), 'jenis_sk' => 'Keuangan dan Operasional', 'perihal' => 'Tim pengelola BOS', 'nama_peminta' => 'Bendahara'])
            ->assertHasNoActionErrors()
            ->assertDispatched('sk-numbers-changed');

        Livewire::test(SkYearOverview::class)->assertSee("7 TAHUN {$year}")->assertSee("8 TAHUN {$year}");
    }

    public function test_summary_says_when_the_year_is_used_up(): void
    {
        $year = now()->year;
        SuratKeputusan::create(['tahun' => $year, 'nomor_urut' => 9999, 'nomor_sk' => "9999 TAHUN {$year}", 'tanggal_surat' => now(), 'perihal' => 'Terakhir', 'jenis_sk' => 'Keuangan dan Operasional', 'nama_peminta' => 'Uji']);

        Livewire::test(SkYearOverview::class)->assertSee('Habis')->assertSee('sudah mencapai 9999');
    }
}
