<?php

namespace Tests\Feature;

use App\Filament\Resources\PersuratanMasterResource;
use App\Filament\Resources\PersuratanMasterResource\Pages\ListPersuratanMasters;
use App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar;
use App\Models\PersuratanMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
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
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

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
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
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
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
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
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

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
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        Livewire::test(ListPersuratanMasters::class)
            ->callAction('create', ['type' => 'klasifikasi', 'kode' => ' sr.01 ', 'nama' => '  Sarana   Prasarana ', 'sort' => 1, 'is_active' => true])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('persuratan_masters', ['type' => 'klasifikasi', 'kode' => 'SR.01', 'nama' => 'Sarana Prasarana']);
    }

    public function test_empty_list_invites_adding_a_choice(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
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
}
