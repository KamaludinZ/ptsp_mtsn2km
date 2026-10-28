<?php

namespace Tests\Feature;

use App\Filament\Pages\Reports\SkRegisterRecap;
use App\Filament\Resources\SuratKeluarResource;
use App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar;
use App\Models\SuratKeluar;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Support\SkRegisterRecap as Recap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** SK numbered in the buku surat keluar by mistake: marked in the list and counted in Rekap Register SK. */
class SkInSuratKeluarTest extends TestCase
{
    use RefreshDatabase;

    private SuratKeluar $sk;

    private SuratKeluar $tugas;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        $year = now()->year;
        $letter = fn (int $urut, string $jenis) => SuratKeluar::create(['tahun' => $year, 'nomor_urut' => $urut, 'nomor_surat' => "T-{$urut}/{$year}",
            'tanggal_surat' => "{$year}-03-01", 'perihal' => 'Uji', 'jenis_surat' => $jenis]);
        $this->sk = $letter(900, 'Surat Keputusan');
        $letter(901, 'SK Panitia PPDB');
        $this->tugas = $letter(902, 'Surat Tugas');
    }

    public function test_the_scope_matches_the_php_rule(): void
    {
        $matched = SuratKeluar::query()->numberedAsSk()->pluck('jenis_surat')->unique()->sort()->values()->all();

        $this->assertSame(['SK Panitia PPDB', 'Surat Keputusan'], $matched);
        $this->assertTrue(SuratKeluar::query()->numberedAsSk()->whereKey($this->sk->id)->exists());
        $this->assertFalse(SuratKeluar::query()->numberedAsSk()->whereKey($this->tugas->id)->exists());
    }

    public function test_the_list_marks_and_filters_letters_that_are_sk(): void
    {
        Livewire::test(ListSuratKeluar::class)
            ->assertTableColumnFormattedStateSet('jenis_surat', 'Surat Keputusan', $this->sk)
            ->filterTable('tercatat_sebagai_sk', true)
            ->assertCanSeeTableRecords([$this->sk])
            ->assertCanNotSeeTableRecords([$this->tugas])
            ->assertCountTableRecords(2);

        $column = Livewire::test(ListSuratKeluar::class)->instance()->getTable()->getColumn('jenis_surat');
        $this->assertSame('heroicon-m-exclamation-triangle', $column->record($this->sk)->getIcon('Surat Keputusan'));
        $this->assertNull($column->record($this->tugas)->getIcon('Surat Tugas'));
    }

    public function test_the_recap_warns_even_when_the_sk_register_is_empty(): void
    {
        $this->assertSame(2, Recap::build(now()->year)['surat_keluar_sk']);
        SuratKeputusan::withDeletesAllowed(fn () => SuratKeputusan::query()->delete());

        $url = SuratKeluarResource::getUrl('index', ['tableFilters' => ['tahun' => ['value' => now()->year], 'tercatat_sebagai_sk' => ['isActive' => true]]]);
        $this->get(SkRegisterRecap::getUrl())->assertOk()
            ->assertSee('Belum ada nomor SK tahun ' . now()->year)
            ->assertSee('2 surat di buku surat keluar tahun ' . now()->year . ' berjenis Surat Keputusan.')
            ->assertSee(e($url), false);

        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        Livewire::withQueryParams($query)->test(ListSuratKeluar::class)->assertCountTableRecords(2);
    }
}
