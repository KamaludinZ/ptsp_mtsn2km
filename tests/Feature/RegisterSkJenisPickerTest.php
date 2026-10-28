<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\PersuratanMaster;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** The Jenis SK picker in Minta Nomor SK. */
class RegisterSkJenisPickerTest extends TestCase
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

    private function picker(\Livewire\Features\SupportTesting\Testable $test): \Filament\Forms\Components\Select
    {
        return collect($test->instance()->getMountedActionForm()->getFlatComponents())
            ->first(fn ($component) => $component instanceof \Filament\Forms\Components\Select && $component->getName() === 'jenis_sk');
    }

    public function test_picker_lists_the_active_master_jenis_in_order(): void
    {
        PersuratanMaster::create(['type' => 'jenis_sk', 'nama' => 'Jenis Nonaktif', 'is_active' => false]);
        $test = Livewire::test(ListSuratKeputusan::class)->mountAction('reserve');

        $this->assertSame(Persuratan::JENIS_SK, array_keys($this->picker($test)->getOptions()));
        $test->assertSee('Pilih klasifikasi SK');
    }

    public function test_choosing_a_jenis_shows_how_it_was_used_this_year(): void
    {
        $year = now()->year;

        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->setActionData(['jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan'])
            ->assertSee("2 SK jenis ini pada {$year}; terakhir 6 TAHUN {$year}.")
            ->setActionData(['tanggal_surat' => ($year + 1) . '-01-10'])
            ->assertSee('Belum ada SK jenis ini pada ' . ($year + 1) . '.');

        $this->assertSame(['jumlah' => 1, 'terakhir' => "5 TAHUN {$year}"], SuratKeputusanService::jenisUsage('Keuangan dan Operasional', $year));
        $this->assertSame(['jumlah' => 0, 'terakhir' => null], SuratKeputusanService::jenisUsage('Keuangan dan Operasional', $year - 1));
    }

    public function test_without_active_jenis_the_officer_is_told_where_to_add_one(): void
    {
        PersuratanMaster::where('type', 'jenis_sk')->update(['is_active' => false]);

        $this->assertSame('Belum ada jenis SK aktif. Tambahkan lewat Master Persuratan > Jenis SK.', SuratKeputusanResource::jenisHelp(null, now()->year));

        Livewire::test(ListSuratKeputusan::class)->mountAction('reserve')
            ->assertSee('Belum ada jenis SK aktif');
    }

    public function test_picker_links_to_the_jenis_sk_tab(): void
    {
        Livewire::test(ListSuratKeputusan::class)->mountAction('reserve')
            ->assertSee('Kelola jenis SK')
            ->assertSeeHtml('master-persuratan?activeTab=jenis_sk');
    }

    public function test_editing_counts_in_the_sk_year(): void
    {
        $year = now()->year;
        $old = SuratKeputusan::create(['tahun' => $year - 1, 'nomor_urut' => 9, 'nomor_sk' => '9 TAHUN ' . ($year - 1), 'tanggal_surat' => ($year - 1) . '-02-01', 'perihal' => 'SK lama', 'jenis_sk' => 'Keuangan dan Operasional', 'nama_peminta' => 'Uji']);

        Livewire::test(ListSuratKeputusan::class)
            ->removeTableFilter('tahun')
            ->mountTableAction('edit', $old)
            ->assertSee('1 SK jenis ini pada ' . ($year - 1) . '; terakhir 9 TAHUN ' . ($year - 1) . '.');
    }
}
