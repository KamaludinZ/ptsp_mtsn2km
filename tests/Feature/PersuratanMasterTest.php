<?php

namespace Tests\Feature;

use App\Filament\Resources\PersuratanMasterResource;
use App\Filament\Resources\PersuratanMasterResource\Pages\ListPersuratanMasters;
use App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar;
use App\Models\PersuratanMaster;
use App\Models\User;
use App\Support\Persuratan;
use Database\Seeders\PersuratanMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/** Master persuratan: routine choices for letters and dispositions. */
class PersuratanMasterTest extends TestCase
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

    public function test_every_list_starts_with_the_routine_choices(): void
    {
        foreach (array_keys(PersuratanMaster::TYPES) as $type) {
            $this->assertTrue(PersuratanMaster::ofType($type)->exists(), "$type has no default choices");
        }
        $this->assertSame('PP.00', PersuratanMaster::ofType('klasifikasi')->first()->value);
    }

    public function test_back_office_browses_the_lists_by_tab(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'))
            ->get(PersuratanMasterResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Instruksi Disposisi');

        $tembusan = PersuratanMaster::where('type', 'tembusan')->get();
        $klasifikasi = PersuratanMaster::where('type', 'klasifikasi')->get();

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', 'tembusan')
            ->assertCanSeeTableRecords($tembusan)
            ->assertCanNotSeeTableRecords($klasifikasi);
    }

    public function test_front_desk_cannot_open_the_lists(): void
    {
        $this->actingAs($this->user('loket1@mtsn2malang.sch.id'))
            ->get(PersuratanMasterResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_officer_adds_a_choice_to_the_active_tab(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', 'klasifikasi')
            ->callAction('create', ['type' => 'klasifikasi', 'kode' => 'SR.00', 'nama' => 'Sarana dan Prasarana', 'sort' => 9, 'is_active' => true])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('persuratan_masters', ['type' => 'klasifikasi', 'kode' => 'SR.00', 'nama' => 'Sarana dan Prasarana']);

        Livewire::test(ListPersuratanMasters::class)
            ->callAction('create', ['type' => 'klasifikasi', 'kode' => null, 'nama' => 'Tanpa kode'])
            ->assertHasActionErrors(['kode' => 'required']);
    }

    public function test_lists_can_be_searched(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $archive = PersuratanMaster::where('nama', 'Arsip')->firstOrFail();
        $headmaster = PersuratanMaster::where('nama', 'Kepala Madrasah')->firstOrFail();

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', 'tembusan')
            ->searchTable('arsip')
            ->assertCanSeeTableRecords([$archive])
            ->assertCanNotSeeTableRecords([$headmaster]);
    }

    public function test_officer_edits_a_choice_and_duplicates_are_refused(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));
        $archive = PersuratanMaster::where('type', 'tembusan')->where('nama', 'Arsip')->firstOrFail();

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', 'tembusan')
            ->callTableAction('edit', $archive, ['type' => 'tembusan', 'nama' => 'Arsip Tata Usaha', 'sort' => 5, 'is_active' => true])
            ->assertHasNoTableActionErrors();
        $this->assertSame('Arsip Tata Usaha', $archive->fresh()->nama);

        Livewire::test(ListPersuratanMasters::class)
            ->callAction('create', ['type' => 'tembusan', 'nama' => 'Kepala Madrasah'])
            ->assertHasActionErrors(['nama' => 'unique']);
    }

    public function test_choices_can_be_deactivated_and_deleted(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));
        $choices = PersuratanMaster::where('type', 'tembusan')->get();
        $first = $choices->first();

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', 'tembusan')
            ->callTableBulkAction('deactivate', $choices);
        $this->assertFalse(PersuratanMaster::ofType('tembusan')->exists());

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', 'tembusan')
            ->callTableAction('delete', $first);
        $this->assertModelMissing($first);
    }

    public function test_forms_offer_active_master_choices(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        PersuratanMaster::create(['type' => 'tujuan_naskah', 'nama' => 'Kepala MAN 1 Kota Malang']);
        PersuratanMaster::create(['type' => 'tujuan_naskah', 'nama' => 'Instansi Lama', 'is_active' => false]);

        Livewire::test(ListSuratKeluar::class)
            ->mountAction('reserve')
            ->assertSee('Kepala MAN 1 Kota Malang')
            ->assertDontSee('Instansi Lama');
    }

    public function test_near_duplicates_and_malformed_codes_are_refused(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        Livewire::test(ListPersuratanMasters::class)
            ->callAction('create', ['type' => 'tembusan', 'nama' => '  kepala   madrasah '])
            ->assertHasActionErrors(['nama']);

        Livewire::test(ListPersuratanMasters::class)
            ->callAction('create', ['type' => 'klasifikasi', 'kode' => 'pp.00', 'nama' => 'Pendidikan Dasar'])
            ->assertHasActionErrors(['kode']);

        Livewire::test(ListPersuratanMasters::class)
            ->callAction('create', ['type' => 'klasifikasi', 'kode' => 'PP 01!', 'nama' => 'Pendidikan Dasar'])
            ->assertHasActionErrors(['kode' => 'regex']);

        Livewire::test(ListPersuratanMasters::class)
            ->callAction('create', ['type' => 'tembusan', 'nama' => 'TU'])
            ->assertHasActionErrors(['nama' => 'min']);
    }

    public function test_values_are_tidied_before_saving(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        Livewire::test(ListPersuratanMasters::class)
            ->callAction('create', ['type' => 'klasifikasi', 'kode' => ' sr.01 ', 'nama' => '  Sarana   Prasarana ', 'sort' => 1, 'is_active' => true])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('persuratan_masters', ['type' => 'klasifikasi', 'kode' => 'SR.01', 'nama' => 'Sarana Prasarana']);
    }

    public function test_empty_list_invites_adding_a_choice(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));
        PersuratanMaster::where('type', 'tembusan')->delete();

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', 'tembusan')
            ->assertSee('Belum ada pilihan tembusan')
            ->callTableAction('emptyCreate', data: ['type' => 'tembusan', 'nama' => 'Komite Madrasah', 'sort' => 0, 'is_active' => true])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('persuratan_masters', ['type' => 'tembusan', 'nama' => 'Komite Madrasah']);

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', 'tembusan')
            ->searchTable('tidak ada')
            ->assertSee('Tidak ada pilihan yang cocok');
    }

    public function test_staff_read_active_choices_through_the_api(): void
    {
        PersuratanMaster::create(['type' => 'jenis_surat', 'nama' => 'Surat Kuasa']);
        PersuratanMaster::create(['type' => 'jenis_surat', 'nama' => 'Jenis Lama', 'is_active' => false]);
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'));

        $jenis = $this->getJson('/api/persuratan/pilihan?jenis=jenis_surat')->assertOk()->json('jenis_surat');
        $this->assertContains('Undangan', $jenis);
        $this->assertContains('Surat Kuasa', $jenis);
        $this->assertNotContains('Jenis Lama', $jenis);

        $this->getJson('/api/persuratan/pilihan')->assertOk()->assertJsonStructure(array_keys(PersuratanMaster::TYPES));
        $this->assertContains('PP.00', $this->getJson('/api/persuratan/pilihan?jenis=klasifikasi')->json('klasifikasi'));
    }

    public function test_seeder_restores_defaults_without_duplicates(): void
    {
        $before = PersuratanMaster::count();
        PersuratanMaster::where('type', 'tembusan')->where('nama', 'Arsip')->delete();
        PersuratanMaster::where('type', 'tembusan')->where('nama', 'Kepala Madrasah')->update(['is_active' => false]);

        $this->seed(\Database\Seeders\PersuratanMasterSeeder::class);

        $this->assertSame($before, PersuratanMaster::count());
        $this->assertFalse(PersuratanMaster::where('type', 'tembusan')->where('nama', 'Kepala Madrasah')->value('is_active'));
    }

    public function test_masters_are_managed_through_the_api(): void
    {
        Sanctum::actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        $id = $this->postJson('/api/persuratan/master', ['jenis' => 'klasifikasi', 'kode' => 'SR.00', 'nama' => 'Sarana Prasarana'])
            ->assertCreated()->assertJsonPath('nilai', 'SR.00')->json('id');
        $this->postJson('/api/persuratan/master', ['jenis' => 'klasifikasi', 'nama' => 'Tanpa Kode'])->assertJsonValidationErrors('kode');
        $this->postJson('/api/persuratan/master', ['jenis' => 'tembusan', 'nama' => 'Arsip'])->assertJsonValidationErrors('nama');

        $this->putJson('/api/persuratan/master/' . $id, ['nama' => 'Sarana dan Prasarana', 'aktif' => false])
            ->assertOk()->assertJsonPath('nama', 'Sarana dan Prasarana')->assertJsonPath('aktif', false);
        $this->assertNotContains('SR.00', Persuratan::options('klasifikasi'));

        $this->getJson('/api/persuratan/master?jenis=klasifikasi')->assertOk()->assertJsonFragment(['kode' => 'SR.00']);
        $this->deleteJson('/api/persuratan/master/' . $id)->assertNoContent();
        $this->assertDatabaseMissing('persuratan_masters', ['id' => $id]);

        Sanctum::actingAs($this->user('loket1@mtsn2malang.sch.id'));
        $this->postJson('/api/persuratan/master', ['jenis' => 'tembusan', 'nama' => 'X'])->assertForbidden();
    }

    public function test_master_list_api_searches_and_paginates(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'));
        PersuratanMaster::where('type', 'tembusan')->where('nama', 'Arsip')->update(['is_active' => false]);

        $this->getJson('/api/persuratan/master?q=arsip')->assertOk()->assertJsonPath('total', 2); // Arsip + "Untuk diarsipkan"
        $this->getJson('/api/persuratan/master?q=arsip&jenis=tembusan')->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.nama', 'Arsip');
        $this->getJson('/api/persuratan/master?q=PP.00')->assertOk()->assertJsonPath('data.0.kode', 'PP.00');
        $this->getJson('/api/persuratan/master?jenis=tembusan&aktif=0')->assertOk()->assertJsonPath('total', 1);

        $page = $this->getJson('/api/persuratan/master?per_halaman=5&halaman=1')->assertOk();
        $this->assertCount(5, $page->json('data'));
        $this->assertSame(PersuratanMaster::count(), $page->json('total'));
        $this->assertSame(2, $this->getJson('/api/persuratan/master?per_halaman=5&page=2')->json('halaman'));
    }

    public function test_back_office_reads_but_only_managers_change_the_lists(): void
    {
        $choice = PersuratanMaster::firstOrFail();

        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $this->getJson('/api/persuratan/master')->assertOk();
        $this->postJson('/api/persuratan/master', ['jenis' => 'tembusan', 'nama' => 'Baru'])->assertForbidden();
        $this->putJson('/api/persuratan/master/' . $choice->id, ['aktif' => false])->assertForbidden();
        $this->deleteJson('/api/persuratan/master/' . $choice->id)->assertForbidden();

        Sanctum::actingAs($this->user('katu@mtsn2malang.sch.id'));
        $this->postJson('/api/persuratan/master', ['jenis' => 'tembusan', 'nama' => 'Baru'])->assertCreated();

        Sanctum::actingAs(tap(User::factory()->create())->assignRole('tata_usaha'));
        $this->putJson('/api/persuratan/master/' . $choice->id, ['urutan' => 9])->assertOk();
    }

    public function test_back_office_sees_the_list_page_without_edit_actions(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        Livewire::test(ListPersuratanMasters::class)
            ->assertOk()
            ->assertActionHidden('create')
            ->assertTableActionHidden('edit', PersuratanMaster::firstOrFail());
    }
}
