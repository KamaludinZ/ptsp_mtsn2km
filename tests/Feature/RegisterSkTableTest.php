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

/** The SK register table, on the demo SK seeded outside production. */
class RegisterSkTableTest extends TestCase
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

    private function sk(int $tahun, int $urut, array $data = []): SuratKeputusan
    {
        return SuratKeputusan::create([
            'tahun' => $tahun,
            'nomor_urut' => $urut,
            'nomor_sk' => SuratKeputusan::composeNumber($urut, $tahun),
            'tanggal_surat' => "{$tahun}-03-01",
            'perihal' => 'SK uji ' . $urut,
            'jenis_sk' => 'Keuangan dan Operasional',
            'nama_peminta' => 'Peminta Uji',
            ...$data,
        ]);
    }

    public function test_demo_register_holds_this_years_sk_in_the_sk_format(): void
    {
        $year = now()->year;
        $demo = SuratKeputusan::orderBy('nomor_urut')->get();

        $this->assertCount(6, $demo);
        $this->assertSame("1 TAHUN {$year}", $demo->first()->nomor_sk);
        $this->assertSame(range(1, 6), $demo->pluck('nomor_urut')->all());
        $this->assertTrue($demo->every(fn (SuratKeputusan $sk) => in_array($sk->jenis_sk, \App\Support\Persuratan::JENIS_SK, true)));
        $this->assertSame(0, \App\Models\SuratKeluar::where('nomor_surat', "1 TAHUN {$year}")->count());
    }

    public function test_table_shows_newest_numbers_first_across_years(): void
    {
        $old = $this->sk(now()->year - 1, 250);
        $latest = SuratKeputusan::where('tahun', now()->year)->orderByDesc('nomor_urut')->firstOrFail();
        $first = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 1)->firstOrFail();

        Livewire::test(ListSuratKeputusan::class)
            ->removeTableFilter('tahun')
            ->assertCanSeeTableRecords([$latest, $first, $old], inOrder: true)
            ->assertSee($latest->nomor_sk)
            ->assertSee($latest->perihal)
            ->assertSee($latest->nama_peminta);
    }

    public function test_columns_of_the_register(): void
    {
        Livewire::test(ListSuratKeputusan::class)
            ->assertTableColumnExists('nomor_urut')
            ->assertTableColumnExists('nomor_sk')
            ->assertTableColumnExists('tanggal_surat')
            ->assertTableColumnExists('perihal')
            ->assertTableColumnExists('jenis_sk')
            ->assertTableColumnExists('nama_peminta')
            ->assertTableColumnExists('pembuat.name')
            ->assertTableColumnExists('status')
            ->assertTableColumnStateSet('jenis_sk', 'Kepegawaian dan Pengangkatan', SuratKeputusan::where('nomor_urut', 1)->first());
    }

    public function test_status_column_tells_which_sk_is_archived(): void
    {
        $withScan = $this->sk(now()->year, 40, ['berkas_scan' => 'surat-keputusan/40.pdf', 'berkas_scan_nama' => 'SK 40.pdf']);
        $without = SuratKeputusan::where('nomor_urut', 1)->firstOrFail();

        $this->assertSame('terarsip', $withScan->status());
        $this->assertSame('menunggu_scan', $without->status());

        Livewire::test(ListSuratKeputusan::class)
            ->assertTableColumnStateSet('status', 'terarsip', $withScan)
            ->assertTableColumnStateSet('status', 'menunggu_scan', $without)
            ->assertTableColumnFormattedStateSet('status', 'Terarsip', $withScan)
            ->assertTableColumnFormattedStateSet('status', 'Menunggu scan', $without);
    }

    public function test_status_filter_separates_archived_from_waiting_sk(): void
    {
        $archived = collect([
            $this->sk(now()->year, 40, ['berkas_scan' => 'surat-keputusan/40.pdf']),
            $this->sk(now()->year, 41, ['berkas_scan' => 'surat-keputusan/41.pdf']),
        ]);
        $blank = $this->sk(now()->year, 42, ['berkas_scan' => '']);
        $this->assertNull($blank->fresh()->berkas_scan);
        $waiting = SuratKeputusan::where('tahun', now()->year)->whereNull('berkas_scan')->get();

        Livewire::test(ListSuratKeputusan::class)
            ->assertTableFilterExists('status')
            ->filterTable('status', 'terarsip')
            ->assertCanSeeTableRecords($archived)
            ->assertCanNotSeeTableRecords($waiting)
            ->filterTable('status', 'menunggu_scan')
            ->assertCanSeeTableRecords($waiting)
            ->assertCanNotSeeTableRecords($archived)
            ->resetTableFilters()
            ->assertCanSeeTableRecords($archived->merge($waiting));

        $this->assertSame(2, SuratKeputusan::withStatus('terarsip')->count());
        $this->assertSame($waiting->count(), SuratKeputusan::withStatus('menunggu_scan')->count());
    }

    public function test_status_combines_with_year_and_jenis(): void
    {
        $match = $this->sk(now()->year - 1, 5, ['jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan']);
        $archivedSameYear = $this->sk(now()->year - 1, 6, ['jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan', 'berkas_scan' => 'surat-keputusan/6.pdf']);
        $thisYear = SuratKeputusan::where('tahun', now()->year)->where('jenis_sk', 'Kepanitiaan dan Tim Pelaksana Kegiatan')->get();

        Livewire::test(ListSuratKeputusan::class)
            ->filterTable('tahun', now()->year - 1)
            ->filterTable('jenis_sk', ['Kepanitiaan dan Tim Pelaksana Kegiatan'])
            ->filterTable('status', 'menunggu_scan')
            ->assertCanSeeTableRecords([$match])
            ->assertCanNotSeeTableRecords($thisYear->push($archivedSameYear));
    }

    public function test_register_opens_on_this_year_and_switches_years(): void
    {
        $old = $this->sk(now()->year - 1, 250);
        $current = SuratKeputusan::where('tahun', now()->year)->get();

        Livewire::test(ListSuratKeputusan::class)
            ->assertTableFilterExists('tahun')
            ->assertCanSeeTableRecords($current)
            ->assertCanNotSeeTableRecords([$old])
            ->filterTable('tahun', now()->year - 1)
            ->assertCanSeeTableRecords([$old])
            ->assertCanNotSeeTableRecords($current);

        $this->assertSame([now()->year => (string) now()->year, now()->year - 1 => (string) (now()->year - 1)], \App\Filament\Resources\SuratKeputusanResource::yearOptions());
    }

    public function test_year_filter_offers_this_year_even_without_sk(): void
    {
        SuratKeputusan::withDeletesAllowed(fn () => SuratKeputusan::query()->delete());
        $this->sk(2024, 1);

        $this->assertSame([now()->year => (string) now()->year, 2024 => '2024'], \App\Filament\Resources\SuratKeputusanResource::yearOptions());
    }

    public function test_jenis_filter_narrows_to_one_or_more_jenis(): void
    {
        $panitia = SuratKeputusan::where('jenis_sk', 'Kepanitiaan dan Tim Pelaksana Kegiatan')->get();
        $keuangan = SuratKeputusan::where('jenis_sk', 'Keuangan dan Operasional')->get();
        $others = SuratKeputusan::whereNotIn('jenis_sk', ['Kepanitiaan dan Tim Pelaksana Kegiatan', 'Keuangan dan Operasional'])->get();

        $this->assertCount(2, $panitia);

        Livewire::test(ListSuratKeputusan::class)
            ->filterTable('jenis_sk', ['Kepanitiaan dan Tim Pelaksana Kegiatan'])
            ->assertCanSeeTableRecords($panitia)
            ->assertCanNotSeeTableRecords($keuangan->merge($others))
            ->filterTable('jenis_sk', ['Kepanitiaan dan Tim Pelaksana Kegiatan', 'Keuangan dan Operasional'])
            ->assertCanSeeTableRecords($panitia->merge($keuangan))
            ->assertCanNotSeeTableRecords($others);
    }

    public function test_jenis_filter_lists_master_order_then_jenis_only_on_old_sk(): void
    {
        $this->sk(now()->year, 90, ['jenis_sk' => 'Jenis Lama']);
        \App\Models\PersuratanMaster::where('type', 'jenis_sk')->where('nama', 'Keuangan dan Operasional')->update(['is_active' => false]);

        $this->assertSame(
            [...array_slice(\App\Support\Persuratan::JENIS_SK, 0, 4), 'Jenis Lama', 'Keuangan dan Operasional'],
            array_keys(\App\Filament\Resources\SuratKeputusanResource::jenisOptions()),
        );
    }

    public function test_year_and_jenis_filters_combine(): void
    {
        $oldPanitia = $this->sk(now()->year - 1, 3, ['jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan']);
        $thisYearPanitia = SuratKeputusan::where('tahun', now()->year)->where('jenis_sk', 'Kepanitiaan dan Tim Pelaksana Kegiatan')->get();

        Livewire::test(ListSuratKeputusan::class)
            ->filterTable('tahun', now()->year - 1)
            ->filterTable('jenis_sk', ['Kepanitiaan dan Tim Pelaksana Kegiatan'])
            ->assertCanSeeTableRecords([$oldPanitia])
            ->assertCanNotSeeTableRecords($thisYearPanitia);
    }

    public function test_search_finds_by_sequence_number_subject_or_requester(): void
    {
        $match = $this->sk(now()->year, 77, ['perihal' => 'Tim adiwiyata madrasah', 'nama_peminta' => 'Pembina Adiwiyata']);
        $other = SuratKeputusan::where('nomor_urut', 1)->firstOrFail();

        Livewire::test(ListSuratKeputusan::class)->searchTable('77')
            ->assertCanSeeTableRecords([$match])->assertCanNotSeeTableRecords([$other]);
        Livewire::test(ListSuratKeputusan::class)->searchTable('adiwiyata')
            ->assertCanSeeTableRecords([$match])->assertCanNotSeeTableRecords([$other]);
        Livewire::test(ListSuratKeputusan::class)->searchTable('pembina')
            ->assertCanSeeTableRecords([$match])->assertCanNotSeeTableRecords([$other]);
    }
}
