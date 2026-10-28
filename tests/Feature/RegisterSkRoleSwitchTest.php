<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Ganti peran and ganti akun sementara on the SK register: the active role of the account in use decides. */
class RegisterSkRoleSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    public function test_an_api_client_learns_where_to_switch_and_switches(): void
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole(['front_desk', 'back_office']);
        $user->forceFill(['active_role_id' => Role::findByName('front_desk', 'web')->id])->save();
        Sanctum::actingAs($user = $user->fresh(), ['*']);

        $this->getJson('/api/peran-aktif')->assertOk()
            ->assertJsonPath('aktif.nama', 'front_desk')
            ->assertJsonPath('aktif.register_sk', false)
            ->assertJsonPath('wewenang.register_sk', ['lihat' => false, 'minta_nomor' => false, 'ubah' => false, 'hapus' => false]);
        $roles = collect($this->getJson('/api/peran-aktif')->json('peran'))->pluck('register_sk', 'nama')->all();
        $this->assertSame(['back_office' => true, 'front_desk' => false], collect($roles)->sortKeys()->all());

        $this->getJson('/api/penomoran-sk')->assertForbidden()->assertJsonPath('peran_yang_berwenang', ['back_office']);

        $this->putJson('/api/peran-aktif', ['peran' => 'back_office'])->assertOk()
            ->assertJsonPath('aktif.nama', 'back_office')
            ->assertJsonPath('wewenang.register_sk.minta_nomor', true);
        Sanctum::actingAs($user->fresh(), ['*']);
        $this->getJson('/api/penomoran-sk')->assertOk();

        $this->assertTrue(Activity::inLog('role')->where('causer_id', $user->id)->exists());
    }

    public function test_a_borrowed_account_works_the_register_with_its_own_role_not_the_administrator_s(): void
    {
        $admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
        $officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $sk = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 1)->firstOrFail();

        // As administrator: deleting is offered.
        $this->actingAs($admin);
        Livewire::test(ListSuratKeputusan::class)->assertTableActionVisible('delete', $sk);

        // In the officer's account: the officer's back-office role, no deleting.
        $this->post(route('ganti-akun.mulai', $officer), ['alasan' => 'Meninjau register SK petugas']);
        $this->assertTrue(auth()->user()->is($officer));
        Livewire::test(ListSuratKeputusan::class)
            ->assertTableActionVisible('edit', $sk)
            ->assertTableActionHidden('delete', $sk);
    }
}
