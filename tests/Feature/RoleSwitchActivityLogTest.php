<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ActiveRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Perpindahan peran tercatat di activity_log ("role"). */
class RoleSwitchActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        $user = User::factory()->create(['name' => 'Bu Sari']);
        foreach (['kepala_tu', 'front_desk'] as $role) {
            $user->assignRole(Role::findOrCreate($role, 'web'));
        }

        return $user->fresh();
    }

    public function test_switching_is_recorded_with_from_to_and_where(): void
    {
        $user = $this->staff();
        $this->actingAs($user)->withSession([ActiveRoles::SESSION_KEY => 'front_desk'])
            ->post('/peran-aktif/ganti', ['peran' => 'kepala_tu', 'kembali' => '/cp/pimpinan/disposisi'], ['Referer' => url('/cp/tiket/5')]);

        $entry = Activity::inLog('role')->sole();
        $this->assertSame('switched', $entry->event);
        $this->assertTrue($entry->causer->is($user));
        $this->assertSame('Berpindah peran Front Desk → Kepala Tata Usaha', $entry->description);
        $this->assertSame('front_desk', $entry->properties['dari']);
        $this->assertSame('kepala_tu', $entry->properties['ke']);
        $this->assertSame('web', $entry->properties['sumber']);
        $this->assertSame('/cp/tiket/5', $entry->properties['halaman']);
        $this->assertNotEmpty($entry->properties['ip']);
    }

    public function test_choosing_after_sign_in_is_recorded_even_for_the_same_role(): void
    {
        $user = $this->staff();
        $user->forceFill(['active_role_id' => Role::findByName('kepala_tu', 'web')->id])->save();

        $this->withSession(['captcha_value' => 'ABCDE'])->post('/login', ['email' => $user->email, 'password' => 'password', 'captcha' => 'ABCDE']);
        $this->post('/peran-aktif/ganti', ['peran' => 'kepala_tu']);

        $entry = Activity::inLog('role')->sole();
        $this->assertSame('chosen', $entry->event);
        $this->assertSame('Memilih peran Kepala Tata Usaha saat masuk', $entry->description);
    }

    public function test_reselecting_the_active_role_is_not_recorded_again(): void
    {
        $user = $this->staff();
        $this->actingAs($user)->withSession([ActiveRoles::SESSION_KEY => 'kepala_tu'])
            ->post('/peran-aktif/ganti', ['peran' => 'kepala_tu']);

        $this->assertSame(0, Activity::inLog('role')->count());
    }

    public function test_api_switches_are_recorded_as_api(): void
    {
        Sanctum::actingAs($this->staff());

        $this->putJson('/api/peran-aktif', ['peran' => 'front_desk'])->assertOk();

        $entry = Activity::inLog('role')->sole();
        $this->assertSame('api', $entry->properties['sumber']);
        $this->assertArrayNotHasKey('halaman', $entry->properties->all());
    }

    public function test_refused_switches_leave_no_trace(): void
    {
        $this->actingAs($this->staff())->post('/peran-aktif/ganti', ['peran' => 'admin']);

        $this->assertSame(0, Activity::inLog('role')->count());
    }
}
