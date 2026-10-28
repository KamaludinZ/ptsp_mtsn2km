<?php

namespace Tests\Feature;

use App\Filament\Resources\PersuratanMasterResource;
use App\Filament\Resources\PersuratanMasterResource\Pages\ListPersuratanMasters;
use App\Models\PersuratanMaster;
use App\Models\User;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Master Persuratan > Jenis SK: the classifications of the SK register. */
class JenisSkMasterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_master_persuratan_has_a_jenis_sk_tab_after_the_letter_tabs(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'))
            ->get(PersuratanMasterResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Jenis SK');

        $tabs = array_keys(Livewire::test(ListPersuratanMasters::class)->instance()->getTabs());

        $this->assertSame(ListPersuratanMasters::JENIS_SK_TAB, end($tabs));
        $this->assertSame(1, count(array_keys($tabs, ListPersuratanMasters::JENIS_SK_TAB, true)));
    }

    public function test_jenis_sk_tab_lists_only_jenis_sk_with_its_own_wording(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $sk = PersuratanMaster::create(['type' => 'jenis_sk', 'nama' => 'Kepanitiaan Uji']);
        $surat = PersuratanMaster::where('type', 'jenis_surat')->get();

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', ListPersuratanMasters::JENIS_SK_TAB)
            ->assertSee('Register Penomoran SK')
            ->assertSee('{N} TAHUN {YYYY}')
            ->assertCanSeeTableRecords([$sk])
            ->assertCanNotSeeTableRecords($surat)
            ->assertTableColumnVisible('nama')
            ->assertTableColumnHidden('kode')
            ->assertTableColumnHidden('format_nomor')
            ->assertTableColumnHidden('type');
    }

    public function test_jenis_sk_tab_lists_the_five_starting_types_in_order(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $defaults = PersuratanMaster::where('type', 'jenis_sk')->orderBy('sort')->get();

        $this->assertSame(Persuratan::JENIS_SK, $defaults->pluck('nama')->all());
        $this->assertTrue($defaults->every->is_active);

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', ListPersuratanMasters::JENIS_SK_TAB)
            ->assertCanSeeTableRecords($defaults, inOrder: true)
            ->assertSee('Keuangan dan Operasional')
            ->assertTableColumnVisible('is_active')
            ->assertTableColumnVisible('sort');
    }

    public function test_officer_adds_a_jenis_sk_from_its_tab(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', ListPersuratanMasters::JENIS_SK_TAB)
            ->assertActionHasLabel('create', 'Tambah jenis SK')
            ->mountAction('create')
            ->assertActionDataSet(['type' => 'jenis_sk', 'is_active' => true])
            ->assertSee('Nama jenis SK')
            ->setActionData(['nama' => '  Kurikulum   dan Akademik ', 'sort' => 5])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('persuratan_masters', ['type' => 'jenis_sk', 'nama' => 'Kurikulum dan Akademik', 'sort' => 5, 'kode' => null]);
    }

    public function test_officer_renames_a_jenis_sk_and_duplicates_are_refused(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $jenis = PersuratanMaster::where('type', 'jenis_sk')->where('nama', 'Keuangan dan Operasional')->firstOrFail();

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', ListPersuratanMasters::JENIS_SK_TAB)
            ->mountTableAction('edit', $jenis)
            ->assertSee('Ubah jenis SK')
            ->setTableActionData(['nama' => 'kepegawaian  dan pengangkatan'])
            ->callMountedTableAction()
            ->assertHasTableActionErrors(['nama']);

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', ListPersuratanMasters::JENIS_SK_TAB)
            ->callTableAction('edit', $jenis, ['nama' => 'Keuangan, Sarana, dan Operasional', 'is_active' => false])
            ->assertHasNoTableActionErrors();

        $this->assertSame('Keuangan, Sarana, dan Operasional', $jenis->fresh()->nama);
        $this->assertFalse($jenis->fresh()->is_active);
        $this->assertNotContains('Keuangan, Sarana, dan Operasional', Persuratan::options('jenis_sk'));
    }

    public function test_a_jenis_sk_name_may_match_a_name_in_another_list(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        PersuratanMaster::create(['type' => 'tujuan_naskah', 'nama' => 'Tim Adiwiyata']);

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', ListPersuratanMasters::JENIS_SK_TAB)
            ->callAction('create', ['type' => 'jenis_sk', 'nama' => 'Tim Adiwiyata', 'sort' => 6, 'is_active' => true])
            ->assertHasNoActionErrors();

        $this->assertSame(2, PersuratanMaster::where('nama', 'Tim Adiwiyata')->count());
    }

    public function test_jenis_sk_are_managed_through_the_api(): void
    {
        \Laravel\Sanctum\Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'));

        $id = $this->postJson('/api/persuratan/master', ['jenis' => 'jenis_sk', 'nama' => 'Kesiswaan', 'urutan' => 9])
            ->assertCreated()->assertJsonPath('jenis', 'jenis_sk')->json('id');
        $this->postJson('/api/persuratan/master', ['jenis' => 'jenis_sk', 'nama' => 'Kesiswaan'])->assertJsonValidationErrors('nama');
        $this->putJson('/api/persuratan/master/' . $id, ['nama' => 'Kesiswaan dan Ekstrakurikuler'])->assertOk();

        $pilihan = $this->getJson('/api/persuratan/pilihan?jenis=jenis_sk')->assertOk()->json('jenis_sk');
        $this->assertSame([...Persuratan::JENIS_SK, 'Kesiswaan dan Ekstrakurikuler'], $pilihan);
    }

    public function test_empty_jenis_sk_tab_invites_adding_one(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));
        PersuratanMaster::where('type', 'jenis_sk')->delete();

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', ListPersuratanMasters::JENIS_SK_TAB)
            ->assertSee('Belum ada jenis SK')
            ->assertSee('mengelompokkan setiap nomor SK');
    }
}
