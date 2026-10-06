<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Kolom konteks peran aktif pada users. */
class ActiveRoleSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_remember_the_chosen_active_role(): void
    {
        $this->assertTrue(Schema::hasColumns('users', ['active_role_id', 'active_role_at']));

        $role = Role::findOrCreate('front_desk', 'web');
        $user = User::factory()->create();
        $user->forceFill(['active_role_id' => $role->id, 'active_role_at' => now()])->save();

        $this->assertSame('front_desk', $user->fresh()->activeRole->name);
        $this->assertNotNull($user->fresh()->active_role_at);
    }

    public function test_deleting_the_role_clears_the_choice_but_keeps_the_user(): void
    {
        $role = Role::findOrCreate('peran_sementara', 'web');
        $user = User::factory()->create();
        $user->forceFill(['active_role_id' => $role->id])->save();

        $role->delete();

        $this->assertNull($user->fresh()->active_role_id);
    }

    public function test_choosing_a_role_does_not_write_the_user_activity_log(): void
    {
        $role = Role::findOrCreate('front_desk', 'web');
        $user = User::factory()->create()->fresh();
        $before = \Spatie\Activitylog\Models\Activity::count();

        $user->forceFill(['active_role_id' => $role->id, 'active_role_at' => now()])->save();

        // Perpindahan peran dicatat tersendiri (Fase 4), bukan sebagai perubahan profil.
        $this->assertSame($before, \Spatie\Activitylog\Models\Activity::count());
    }
}
