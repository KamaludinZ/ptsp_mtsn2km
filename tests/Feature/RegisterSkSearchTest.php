<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Register SK search controls: search box, officer and date filters, kept for the session. */
class RegisterSkSearchTest extends TestCase
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

    private function sk(int $urut, array $data = []): SuratKeputusan
    {
        $year = now()->year;

        return SuratKeputusan::create([
            'tahun' => $year, 'nomor_urut' => $urut, 'nomor_sk' => SuratKeputusan::composeNumber($urut, $year),
            'tanggal_surat' => "{$year}-06-15", 'perihal' => 'SK uji ' . $urut, 'jenis_sk' => 'Keuangan dan Operasional', 'nama_peminta' => 'Uji',
            ...$data,
        ]);
    }

    public function test_search_box_finds_by_full_number_and_by_jenis(): void
    {
        $year = now()->year;
        $target = SuratKeputusan::where('nomor_urut', 4)->firstOrFail();
        $others = SuratKeputusan::where('nomor_urut', '!=', 4)->get();

        Livewire::test(ListSuratKeputusan::class)
            ->assertSee('Cari no. urut, nomor SK, perihal, keterangan/no. tiket, jenis, peminta')
            ->searchTable("4 TAHUN {$year}")
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords($others);

        $tim = SuratKeputusan::where('jenis_sk', 'Pembentukan Tim Khusus dan Regulasi Internal')->get();
        Livewire::test(ListSuratKeputusan::class)
            ->searchTable('regulasi')
            ->assertCanSeeTableRecords($tim)
            ->assertCanNotSeeTableRecords(SuratKeputusan::whereNotIn('id', $tim->pluck('id'))->get());
    }

    public function test_officer_filter_shows_the_numbers_one_officer_took(): void
    {
        $other = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
        $mine = $this->sk(20, ['pembuat_id' => $this->officer->id]);
        $theirs = $this->sk(21, ['pembuat_id' => $other->id]);

        Livewire::test(ListSuratKeputusan::class)
            ->assertTableFilterExists('pembuat_id')
            ->filterTable('pembuat_id', $other->id)
            ->assertCanSeeTableRecords([$theirs])
            ->assertCanNotSeeTableRecords([$mine]);
    }

    public function test_date_filter_narrows_to_a_range_of_letter_dates(): void
    {
        $year = now()->year;
        $june = $this->sk(30, ['tanggal_surat' => "{$year}-06-15"]);
        $july = $this->sk(31, ['tanggal_surat' => "{$year}-07-20"]);

        Livewire::test(ListSuratKeputusan::class)
            ->filterTable('tanggal', ['from' => "{$year}-06-01", 'until' => "{$year}-06-30"])
            ->assertCanSeeTableRecords([$june])
            ->assertCanNotSeeTableRecords([$july])
            ->filterTable('tanggal', ['from' => "{$year}-07-01", 'until' => null])
            ->assertCanSeeTableRecords([$july])
            ->assertCanNotSeeTableRecords([$june]);
    }

    public function test_search_and_filters_stay_when_the_officer_comes_back(): void
    {
        $year = now()->year;

        Livewire::test(ListSuratKeputusan::class)
            ->searchTable('panitia')
            ->filterTable('jenis_sk', ['Kepanitiaan dan Tim Pelaksana Kegiatan']);

        Livewire::test(ListSuratKeputusan::class)
            ->assertSet('tableSearch', 'panitia')
            ->assertSet('tableFilters.jenis_sk.values', ['Kepanitiaan dan Tim Pelaksana Kegiatan'])
            ->assertSet('tableFilters.tahun.value', $year);
    }
}
