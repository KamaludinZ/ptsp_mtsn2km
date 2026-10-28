<?php

namespace Tests\Feature;

use App\Filament\Resources\PersuratanMasterResource\Pages\ListPersuratanMasters;
use App\Models\PersuratanMaster;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\JenisSkService;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/** Jenis SK CRUD API with each jenis' use in the register, in every year and in one year. */
class JenisSkUsageApiTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private string $panitia;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        [, , $this->panitia] = Persuratan::JENIS_SK;

        $year = now()->year - 1;
        SuratKeputusan::create(['tahun' => $year, 'nomor_urut' => 9, 'nomor_sk' => SuratKeputusan::composeNumber(9, $year), 'tanggal_surat' => "{$year}-05-02",
            'perihal' => 'Panitia akhir tahun lalu', 'jenis_sk' => $this->panitia, 'nama_peminta' => 'Waka Kesiswaan']);
    }

    private function jenis(string $nama): PersuratanMaster
    {
        return PersuratanMaster::where('type', 'jenis_sk')->where('nama', $nama)->firstOrFail();
    }

    public function test_usage_counts_every_year_and_one_year_in_one_go(): void
    {
        $usage = JenisSkService::usage([$this->panitia, 'SK Penghargaan', $this->panitia]);

        $this->assertSame([$this->panitia => ['semua' => 3, 'tahun' => 2], 'SK Penghargaan' => ['semua' => 0, 'tahun' => 0]], $usage);
        $this->assertSame(['semua' => 3, 'tahun' => 1], JenisSkService::usage([$this->panitia], now()->year - 1)[$this->panitia]);
        $this->assertSame([], JenisSkService::usage([]));
    }

    public function test_the_list_gives_the_use_of_each_jenis_for_the_year_asked(): void
    {
        Sanctum::actingAs($this->officer, ['*']);
        $row = fn ($response) => collect($response->json('data'))->firstWhere('nama', $this->panitia);

        $thisYear = $this->getJson('/api/persuratan/jenis-sk')->assertOk()->assertJsonPath('tahun', now()->year);
        $this->assertSame([3, 2], [$row($thisYear)['jumlah_sk'], $row($thisYear)['jumlah_sk_tahun']]);

        $lastYear = $this->getJson('/api/persuratan/jenis-sk?tahun=' . (now()->year - 1))->assertOk();
        $this->assertSame([3, 1], [$row($lastYear)['jumlah_sk'], $row($lastYear)['jumlah_sk_tahun']]);

        $this->getJson('/api/persuratan/jenis-sk?tahun=dulu')->assertJsonValidationErrors(['tahun' => 'Tahun harus berupa tahun antara 2000 dan 2100.']);
    }

    public function test_one_jenis_and_a_saved_one_carry_their_use(): void
    {
        Sanctum::actingAs($this->officer, ['*']);

        $this->getJson('/api/persuratan/jenis-sk/' . $this->jenis($this->panitia)->id)->assertOk()
            ->assertJsonPath('jumlah_sk', 3)
            ->assertJsonPath('jumlah_sk_tahun', 2);

        $this->postJson('/api/persuratan/jenis-sk', ['nama' => 'SK Penghargaan'])->assertCreated()
            ->assertJsonPath('jumlah_sk', 0)
            ->assertJsonPath('jumlah_sk_tahun', 0);
    }

    public function test_the_panel_shows_the_same_numbers_as_the_api(): void
    {
        $this->actingAs($this->officer);
        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', ListPersuratanMasters::JENIS_SK_TAB)
            ->assertTableColumnStateSet('pemakaian_sk', '3 SK', $this->jenis($this->panitia))
            ->assertTableColumnHasDescription('pemakaian_sk', '2 SK tahun ' . now()->year, $this->jenis($this->panitia));

        Sanctum::actingAs($this->officer, ['*']);
        $row = collect($this->getJson('/api/persuratan/jenis-sk')->json('data'))->firstWhere('nama', $this->panitia);
        $this->assertSame([3, 2], [$row['jumlah_sk'], $row['jumlah_sk_tahun']]);
    }
}
