<?php

namespace Tests\Feature;

use App\Filament\Resources\PersuratanMasterResource\Pages\ListPersuratanMasters;
use App\Filament\Resources\SuratKeputusanResource;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\PersuratanMaster;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Tab Jenis SK: how much the register uses each jenis, with the register filtered by it a click away. */
class JenisSkUsageColumnTest extends TestCase
{
    use RefreshDatabase;

    private string $panitia;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        [, , $this->panitia] = Persuratan::JENIS_SK;

        $year = now()->year - 1;
        SuratKeputusan::create(['tahun' => $year, 'nomor_urut' => 9, 'nomor_sk' => SuratKeputusan::composeNumber(9, $year), 'tanggal_surat' => "{$year}-05-02",
            'perihal' => 'Panitia akhir tahun lalu', 'jenis_sk' => $this->panitia, 'nama_peminta' => 'Waka Kesiswaan']);
    }

    private function jenis(string $nama): PersuratanMaster
    {
        return PersuratanMaster::where('type', 'jenis_sk')->where('nama', $nama)->firstOrFail();
    }

    public function test_each_jenis_shows_its_sk_in_all_years_and_this_year(): void
    {
        $unused = PersuratanMaster::create(['type' => 'jenis_sk', 'nama' => 'SK Penghargaan', 'is_active' => true, 'sort' => 9]);
        $year = now()->year;

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', ListPersuratanMasters::JENIS_SK_TAB)
            ->assertSee('Dipakai di register')
            ->assertTableColumnStateSet('pemakaian_sk', '3 SK', $this->jenis($this->panitia))
            ->assertTableColumnHasDescription('pemakaian_sk', "2 SK tahun {$year}", $this->jenis($this->panitia))
            ->assertTableColumnStateSet('pemakaian_sk', 'Belum dipakai', $unused)
            ->assertTableColumnHasDescription('pemakaian_sk', "0 SK tahun {$year}", $unused);
    }

    public function test_the_count_leads_to_the_register_filtered_by_the_jenis_in_every_year(): void
    {
        $page = Livewire::test(ListPersuratanMasters::class)->set('activeTab', ListPersuratanMasters::JENIS_SK_TAB);
        $url = $page->instance()->getTable()->getColumn('pemakaian_sk')->record($this->jenis($this->panitia))->getUrl();

        $this->assertStringStartsWith(SuratKeputusanResource::getUrl('index') . '?', $url);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        Livewire::withQueryParams($query)->test(ListSuratKeputusan::class)
            ->assertSee('Register semua tahun')
            ->assertCanSeeTableRecords(SuratKeputusan::where('jenis_sk', $this->panitia)->get())
            ->assertCountTableRecords(3);
    }

    public function test_other_lists_do_not_show_the_column(): void
    {
        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', 'tujuan_naskah')
            ->assertTableColumnHidden('pemakaian_sk')
            ->assertActionHidden('skRecap');
    }

    public function test_the_jenis_sk_tab_leads_to_the_year_recap(): void
    {
        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', ListPersuratanMasters::JENIS_SK_TAB)
            ->assertActionVisible('skRecap')
            ->assertActionHasUrl('skRecap', \App\Filament\Pages\Reports\SkRegisterRecap::getUrl());
    }
}
