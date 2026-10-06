<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** /api/peran-aktif: peran staf yang dipegang akun, peran aktifnya, dan berpindah peran. */
class ActiveRoleApiTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->assignRole(Role::findOrCreate($role, 'web'));
        }

        return $user;
    }

    public function test_requires_sign_in(): void
    {
        $this->getJson('/api/peran-aktif')->assertUnauthorized();
    }

    public function test_applicants_have_no_active_role(): void
    {
        Sanctum::actingAs($this->staff('umum'));

        $this->getJson('/api/peran-aktif')->assertForbidden();
        $this->putJson('/api/peran-aktif', ['peran' => 'umum'])->assertForbidden();
    }

    public function test_staff_switch_to_a_role_they_hold(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        Sanctum::actingAs($user);

        $this->putJson('/api/peran-aktif', ['peran' => 'front_desk'])->assertOk()
            ->assertJsonPath('message', 'Peran aktif: Front Desk.')
            ->assertJsonPath('sebelumnya', null)
            ->assertJsonPath('aktif.nama', 'front_desk')
            ->assertJsonPath('perlu_memilih', false);

        $this->assertSame('front_desk', $user->fresh()->activeRole->name);
        $this->assertNotNull($user->fresh()->active_role_at);

        $this->putJson('/api/peran-aktif', ['peran' => 'kepala_tu'])->assertOk()
            ->assertJsonPath('sebelumnya', 'front_desk')
            ->assertJsonPath('aktif.nama', 'kepala_tu');

        $this->putJson('/api/peran-aktif', ['peran' => 'kepala_tu'])->assertOk()
            ->assertJsonPath('message', 'Peran Kepala Tata Usaha sudah aktif.');
    }

    public function test_cannot_switch_to_a_role_not_held(): void
    {
        $user = $this->staff('front_desk', 'back_office');
        Role::findOrCreate('admin', 'web');
        Sanctum::actingAs($user);

        // Exists but not held, a non-staff role the user holds, unknown, missing.
        $user->assignRole(Role::findOrCreate('guru', 'web'));
        foreach (['admin', 'guru', 'tidak_ada'] as $role) {
            $this->putJson('/api/peran-aktif', ['peran' => $role])->assertUnprocessable()
                ->assertJsonValidationErrors(['peran' => 'Peran ini tidak Anda pegang.']);
        }
        $this->putJson('/api/peran-aktif', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['peran' => 'Pilih peran yang akan diaktifkan.']);

        $this->assertNull($user->fresh()->active_role_id);
    }

    public function test_multi_role_staff_without_a_choice_must_pick_one(): void
    {
        // guru is not a staff role, so it is left out of the list.
        Sanctum::actingAs($this->staff('tata_usaha', 'front_desk', 'guru'));

        $this->getJson('/api/peran-aktif')->assertOk()
            ->assertJsonPath('aktif', null)
            ->assertJsonPath('perlu_memilih', true)
            ->assertJsonCount(2, 'peran')
            // Ordered like User::STAFF_ROLES, not by assignment.
            ->assertJsonPath('peran.0.nama', 'front_desk')
            ->assertJsonPath('peran.0.label', 'Front Desk')
            ->assertJsonPath('peran.0.area', ['Loket'])
            ->assertJsonPath('peran.1.nama', 'tata_usaha')
            ->assertJsonPath('peran.1.aktif', false);
    }

    public function test_chosen_role_is_marked_active_with_its_time(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        $user->forceFill(['active_role_id' => Role::findByName('kepala_tu', 'web')->id, 'active_role_at' => '2026-10-08 07:42:00'])->save();
        Sanctum::actingAs($user);

        $this->getJson('/api/peran-aktif')->assertOk()
            ->assertJsonPath('perlu_memilih', false)
            ->assertJsonPath('aktif.nama', 'kepala_tu')
            ->assertJsonPath('aktif.area', ['Pimpinan', 'Loket', 'Back Office', 'Pengawasan'])
            ->assertJsonPath('aktif.terakhir_dipakai', now()->parse('2026-10-08 07:42:00')->toIso8601String())
            ->assertJsonPath('peran.1.nama', 'kepala_tu')
            ->assertJsonPath('peran.1.aktif', true)
            ->assertJsonPath('peran.0.terakhir_dipakai', null);
    }

    public function test_a_single_staff_role_is_active_without_choosing(): void
    {
        Sanctum::actingAs($this->staff('back_office'));

        $this->getJson('/api/peran-aktif')->assertOk()
            ->assertJsonPath('perlu_memilih', false)
            ->assertJsonPath('aktif.nama', 'back_office');
    }

    public function test_a_role_no_longer_held_is_not_active(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        $user->forceFill(['active_role_id' => Role::findByName('kepala_tu', 'web')->id])->save();
        $user->removeRole('kepala_tu');
        Sanctum::actingAs($user->fresh());

        // One staff role left: that one is the context.
        $this->getJson('/api/peran-aktif')->assertOk()
            ->assertJsonPath('aktif.nama', 'front_desk')
            ->assertJsonCount(1, 'peran');
    }
}
