<?php

namespace Tests\Feature;

use App\Filament\Pages\Reports\SkRegisterRecap;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\Persuratan;
use App\Support\RoleAccess;
use App\Support\SkRegisterRecap as Recap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Rekap Register SK: one year of the register for evaluation. */
class SkRegisterRecapTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
    }

    private function sk(int $nomorUrut): SuratKeputusan
    {
        return SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', $nomorUrut)->firstOrFail();
    }

    /** SK 1 archived two days after its number; SK 6 deleted. */
    private function workTheYear(): void
    {
        $this->actingAs($this->officer);
        $this->travel(2)->days();
        app(SuratKeputusanService::class)->attachScan($this->sk(1), UploadedFile::fake()->createWithContent('SK wali kelas.pdf', '%PDF-1.4'));
        $admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
        app(SuratKeputusanService::class)->delete($this->sk(6), $admin, 'Salah catat, SK dibatalkan');
        $this->travelBack();
    }

    public function test_the_recap_counts_numbers_archive_and_deletions(): void
    {
        $this->workTheYear();
        [$kepegawaian, , $panitia] = Persuratan::JENIS_SK;

        $r = Recap::build(now()->year);

        $this->assertSame([5, 1, 4, 20, '5 TAHUN ' . now()->year, 1], [$r['terbit'], $r['terarsip'], $r['menunggu_scan'], $r['persen_arsip'], $r['nomor_terakhir'], $r['dihapus']]);
        $this->assertEqualsWithDelta(2.0, $r['rata_hari_arsip'], 0.05);
        // Seeded SK are dated 1 Jan, 24 Jan, 16 Feb, 11 Mar and 3 Apr (SK 6, 26 Apr, was deleted).
        $this->assertSame([2, 1, 1, 1, 0], array_column(array_slice($r['per_bulan'], 0, 5), 'terbit'));
        $this->assertSame(['bulan' => 1, 'nama' => 'Januari', 'terbit' => 2, 'terarsip' => 1], $r['per_bulan'][0]);
        $this->assertSame(['jenis' => $kepegawaian, 'terbit' => 1, 'terarsip' => 1, 'aktif' => true], $r['per_jenis'][0]);
        $this->assertContains(['jenis' => $panitia, 'terbit' => 1, 'terarsip' => 0, 'aktif' => true], $r['per_jenis']);
        $maker = $this->sk(1)->pembuat;
        $this->assertSame([['petugas' => $maker->name, 'peran' => RoleAccess::roleLabel('back_office'), 'terbit' => 5, 'terarsip' => 1]], $r['per_petugas']);
    }

    public function test_the_page_shows_the_year_at_a_glance(): void
    {
        $this->workTheYear();
        $year = now()->year;

        $this->actingAs($this->officer)->get(SkRegisterRecap::getUrl())->assertOk()
            ->assertSee('Rekap Register SK')
            ->assertSeeInOrder(['Nomor SK terbit', '5', 'Terakhir: 5 TAHUN ' . $year])
            ->assertSeeInOrder(['Arsip scan lengkap', '20%', '1 dari 5 SK terarsip'])
            ->assertSeeInOrder(['Menunggu scan', '4', 'Lihat SK yang menunggu scan'])
            ->assertSeeInOrder(['Rata-rata sampai terarsip', '2 hari'])
            ->assertSeeInOrder(['SK dihapus', '1'])
            ->assertSeeInOrder(['Per bulan', 'Januari', 'Februari', 'Desember'])
            ->assertSee('Per jenis SK')
            ->assertSee('Per petugas pencatat');
    }

    public function test_another_year_and_an_empty_year(): void
    {
        $year = now()->year - 1;
        SuratKeputusan::create(['tahun' => $year, 'nomor_urut' => 1, 'nomor_sk' => SuratKeputusan::composeNumber(1, $year), 'tanggal_surat' => "{$year}-08-17",
            'perihal' => 'Panitia HUT RI', 'jenis_sk' => Persuratan::JENIS_SK[2], 'nama_peminta' => 'Waka Kesiswaan']);
        $this->actingAs($this->officer);

        Livewire::withQueryParams(['tahun' => $year])->test(SkRegisterRecap::class)
            ->assertSet('year', $year)
            ->assertSeeHtml('data-sk-recap-month="8"')
            ->assertSeeInOrder(['Nomor SK terbit', '1', 'Terakhir: 1 TAHUN ' . $year])
            ->set('year', now()->year)
            ->assertSee('Terakhir: 6 TAHUN ' . now()->year);

        SuratKeputusan::withDeletesAllowed(fn () => SuratKeputusan::query()->where('tahun', now()->year)->delete());
        // (Query params given to Livewire stay for the next test component.)
        Livewire::withQueryParams(['tahun' => now()->year])->test(SkRegisterRecap::class)
            ->assertSeeHtml('data-sk-recap-empty')
            ->assertSee('Belum ada nomor SK tahun ' . now()->year);

        // A year without SK in the URL falls back to this year.
        Livewire::withQueryParams(['tahun' => 1999])->test(SkRegisterRecap::class)->assertSet('year', now()->year);
    }

    public function test_the_jenis_table_helps_keep_master_jenis_sk(): void
    {
        [, , , , $keuangan] = Persuratan::JENIS_SK;
        \App\Models\PersuratanMaster::where('type', 'jenis_sk')->where('nama', $keuangan)->update(['is_active' => false]);
        \App\Models\PersuratanMaster::create(['type' => 'jenis_sk', 'nama' => 'SK Penghargaan', 'is_active' => true, 'sort' => 9]);
        $this->actingAs($this->officer);

        $r = Recap::build(now()->year);
        $this->assertSame(['jenis' => 'SK Penghargaan', 'terbit' => 0, 'terarsip' => 0, 'aktif' => true], collect($r['per_jenis'])->firstWhere('jenis', 'SK Penghargaan'));
        // Switched off, still used this year: listed last.
        $this->assertSame(['jenis' => $keuangan, 'terbit' => 1, 'terarsip' => 0, 'aktif' => false], collect($r['per_jenis'])->last());

        $this->get(SkRegisterRecap::getUrl())->assertOk()
            ->assertSee('data-sk-recap-jenis="' . $keuangan . '" data-aktif="tidak"', false)
            ->assertSeeInOrder(['SK Penghargaan', 'Belum dipakai tahun ini'])
            ->assertSee('Nonaktif')
            ->assertSee('Kelola jenis SK')
            ->assertSee(e(\App\Filament\Resources\PersuratanMasterResource::getUrl('index', ['activeTab' => 'jenis_sk'])), false);
    }

    public function test_the_scans_waiting_longest_are_listed_with_their_age(): void
    {
        $this->workTheYear();
        \Illuminate\Support\Facades\DB::table('surat_keputusan')->where('id', $this->sk(2)->id)->update(['created_at' => now()->subDays(20)]);
        $year = now()->year;

        $r = Recap::build($year);
        $this->assertSame(1, $r['scan_terlambat']);
        $this->assertSame(['nomor_sk' => "2 TAHUN {$year}", 'hari' => 20], \Illuminate\Support\Arr::only($r['menunggu_terlama'][0], ['nomor_sk', 'hari']));
        $this->assertNotContains("1 TAHUN {$year}", array_column($r['menunggu_terlama'], 'nomor_sk'));
        $this->assertCount(4, $r['menunggu_terlama']);

        $this->get(SkRegisterRecap::getUrl())->assertOk()
            ->assertSee('Menunggu scan paling lama')
            ->assertSee('1 SK sudah lewat 14 hari.')
            ->assertSeeInOrder(["2 TAHUN {$year}", 'Lewat 14 hari · 20 hari'])
            ->assertSee(e(\App\Filament\Resources\SuratKeputusanResource::getUrl('view', ['record' => $this->sk(2)])), false)
            ->assertSee('Nomor diambil hari ini');
    }

    public function test_a_fully_archived_year_lists_nothing_to_upload(): void
    {
        $this->actingAs($this->officer);
        foreach ([1, 2, 3, 4, 5, 6] as $nomor) {
            app(SuratKeputusanService::class)->attachScan($this->sk($nomor), UploadedFile::fake()->createWithContent("SK {$nomor}.pdf", '%PDF-1.4'));
        }

        $this->assertSame([], Recap::build(now()->year)['menunggu_terlama']);
        $this->get(SkRegisterRecap::getUrl())->assertOk()
            ->assertDontSee('Menunggu scan paling lama')
            ->assertSee('Semua SK sudah terarsip');
    }

    public function test_only_those_who_work_the_register_open_the_recap(): void
    {
        $this->actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail())
            ->get(SkRegisterRecap::getUrl())
            ->assertForbidden();
    }
}
