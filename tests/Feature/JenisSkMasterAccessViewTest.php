<?php

namespace Tests\Feature;

use App\Filament\Resources\PersuratanMasterResource\Pages\ListPersuratanMasters;
use App\Models\PersuratanMaster;
use App\Models\User;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Tab Jenis SK in Master Persuratan: what the active role may do, and what deleting a jenis leaves behind. */
class JenisSkMasterAccessViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function jenisSkTab(string $email): \Livewire\Features\SupportTesting\Testable
    {
        $this->actingAs(User::where('email', $email)->firstOrFail());

        return Livewire::test(ListPersuratanMasters::class)->set('activeTab', ListPersuratanMasters::JENIS_SK_TAB);
    }

    private function jenis(string $nama): PersuratanMaster
    {
        return PersuratanMaster::where('type', 'jenis_sk')->where('nama', $nama)->firstOrFail();
    }

    public function test_back_office_is_told_deleting_is_for_administrator(): void
    {
        [, , $panitia] = Persuratan::JENIS_SK;

        $this->jenisSkTab('staff1@mtsn2malang.sch.id')
            ->assertSee('Anda dapat menambah dan mengubah jenis SK. Menghapus hanya dari peran Administrator; untuk menyembunyikan jenis, matikan "Aktif".')
            ->assertTableActionVisible('edit', $this->jenis($panitia))
            ->assertTableActionHidden('delete', $this->jenis($panitia));
    }

    public function test_administrator_may_delete_and_sees_how_many_sk_keep_the_jenis(): void
    {
        [, , $panitia] = Persuratan::JENIS_SK;
        $unused = PersuratanMaster::create(['type' => 'jenis_sk', 'nama' => 'SK Penghargaan', 'is_active' => true, 'sort' => 9]);

        $this->jenisSkTab('ptsp@mtsn2malang.sch.id')
            ->assertSee('Sebagai Administrator, Anda juga dapat menghapus jenis SK; SK yang sudah memakainya tetap menyimpan jenisnya.')
            ->mountTableAction('delete', $this->jenis($panitia))
            ->assertSee('Jenis SK ini hilang dari pilihan Minta Nomor SK. 2 SK yang sudah memakainya tetap menyimpan jenisnya.')
            ->callMountedTableAction()
            ->mountTableAction('delete', $unused)
            ->assertSee('Belum ada SK yang memakainya.');

        $this->assertDatabaseMissing('persuratan_masters', ['type' => 'jenis_sk', 'nama' => $panitia]);
        $this->assertSame(2, \App\Models\SuratKeputusan::where('jenis_sk', $panitia)->count());
    }

    public function test_other_lists_keep_their_own_delete_note(): void
    {
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $tujuan = PersuratanMaster::where('type', 'tujuan_naskah')->firstOrFail();

        Livewire::test(ListPersuratanMasters::class)
            ->mountTableAction('delete', $tujuan)
            ->assertSee('Pilihan ini hilang dari form. Surat yang sudah memakainya tidak berubah.')
            ->assertDontSee('Jenis SK ini hilang');
    }
}
