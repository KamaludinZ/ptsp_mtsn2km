<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Petugas pencatat: filled in from the signed-in account and its active role, never from the request. */
class RegisterSkOfficerTest extends TestCase
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

    private function data(array $override = []): array
    {
        return [
            'tanggal_surat' => now()->toDateString(),
            'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan',
            'perihal' => 'Panitia ujian akhir madrasah',
            'nama_peminta' => 'Waka Kurikulum',
            ...$override,
        ];
    }

    /** Back office and administrator in one account, acting as $role. */
    private function twoRoleOfficer(string $role): User
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole(Role::findOrCreate('back_office', 'web'), Role::findOrCreate('admin', 'web'));
        ActiveRoles::switchTo($user->fresh(), $role);

        return $user->fresh();
    }

    public function test_the_request_form_shows_who_will_be_recorded(): void
    {
        $this->actingAs($officer = $this->user('staff1@mtsn2malang.sch.id'));

        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->assertSee('Petugas pencatat')
            ->assertSee($officer->name . ' · ' . RoleAccess::roleLabel('back_office'))
            ->assertSee('Terisi otomatis dari akun dan peran aktif yang sedang masuk; tidak dapat diganti.');
    }

    public function test_taking_a_number_records_the_signed_in_officer_and_active_role(): void
    {
        $officer = $this->twoRoleOfficer('admin');
        $other = $this->user('ptsp@mtsn2malang.sch.id');
        $this->actingAs($officer);

        // Fields that are not on the form are dropped, whatever the browser sends.
        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->data(['pembuat_id' => $other->id, 'peran_pembuat' => 'kepala_tu']))
            ->assertHasNoActionErrors();

        $sk = SuratKeputusan::latest('id')->firstOrFail();
        $this->assertSame($officer->id, $sk->pembuat_id);
        $this->assertSame('admin', $sk->peran_pembuat);
    }

    public function test_the_api_records_the_token_owner_whatever_the_client_sends(): void
    {
        $officer = $this->twoRoleOfficer('back_office');
        $other = $this->user('ptsp@mtsn2malang.sch.id');
        Sanctum::actingAs($officer, ['*']);

        $this->postJson('/api/penomoran-sk', $this->data(['pembuat_id' => $other->id, 'petugas' => $other->name, 'peran_pembuat' => 'admin']))
            ->assertCreated()
            ->assertJsonPath('petugas', $officer->name)
            ->assertJsonPath('peran_petugas.nama', 'back_office');

        $this->assertDatabaseHas('surat_keputusan', ['nomor_urut' => 7, 'tahun' => now()->year, 'pembuat_id' => $officer->id, 'peran_pembuat' => 'back_office']);
    }

    public function test_access_endpoint_tells_who_will_be_recorded(): void
    {
        Sanctum::actingAs($officer = $this->user('staff1@mtsn2malang.sch.id'), ['*']);

        $this->getJson('/api/penomoran-sk/akses')->assertOk()
            ->assertJsonPath('petugas', ['id' => $officer->id, 'nama' => $officer->name])
            ->assertJsonPath('peran_aktif.nama', 'back_office');
    }

    public function test_a_correction_by_someone_else_keeps_the_recorded_officer(): void
    {
        $sk = SuratKeputusan::where('nomor_urut', 1)->firstOrFail();
        $recorded = [$sk->pembuat_id, $sk->peran_pembuat];
        $admin = $this->user('ptsp@mtsn2malang.sch.id');

        $this->actingAs($admin);
        Livewire::test(ListSuratKeputusan::class)
            ->callTableAction('edit', $sk, ['nama_peminta' => 'Kepala Madrasah', 'pembuat_id' => $admin->id])
            ->assertHasNoTableActionErrors();

        Sanctum::actingAs($admin, ['*']);
        $this->putJson("/api/penomoran-sk/{$sk->id}", ['perihal' => 'Pengangkatan wali kelas semester genap', 'pembuat_id' => $admin->id, 'peran_pembuat' => 'admin'])->assertOk();

        $sk->refresh();
        $this->assertSame('Kepala Madrasah', $sk->nama_peminta);
        $this->assertSame($recorded, [$sk->pembuat_id, $sk->peran_pembuat]);
    }

    public function test_the_edit_form_and_table_show_the_recorded_officer_and_role(): void
    {
        $sk = SuratKeputusan::where('nomor_urut', 1)->with('pembuat')->firstOrFail();
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        Livewire::test(ListSuratKeputusan::class)
            ->mountTableAction('edit', $sk)
            ->assertSee($sk->pembuat->name . ' · ' . RoleAccess::roleLabel('back_office'))
            ->assertDontSee('Terisi otomatis dari akun');

        $sk->pembuat->forceDelete();
        Livewire::test(ListSuratKeputusan::class)
            ->mountTableAction('edit', $sk->fresh())
            ->assertSee('Akun sudah dihapus · ' . RoleAccess::roleLabel('back_office'));
    }
}
