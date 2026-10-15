<?php

namespace Tests\Feature;

use App\Filament\Pages\Notifications;
use App\Models\User;
use App\Support\ActiveRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Masuk ke /cp: peran aktif petugas multi-peran langsung ditetapkan, tanpa memilih dulu. */
class ActiveRoleSignInTest extends TestCase
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

    private function signIn(User $user)
    {
        return $this->withSession(['captcha_value' => 'ABCDE'])->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'captcha' => 'ABCDE',
        ]);
    }

    public function test_multi_role_staff_go_straight_in_with_their_last_role_active(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        // A choice from an earlier session is used again right away.
        $user->forceFill(['active_role_id' => Role::findByName('front_desk', 'web')->id])->save();

        $this->signIn($user)->assertRedirect('/cp');
        $this->assertNull(session(ActiveRoles::PICK_FLAG));
        $this->assertSame('front_desk', session(ActiveRoles::SESSION_KEY));

        // /cp opens without a detour to the picker; switching still works.
        $this->get(Notifications::getUrl(panel: 'admin'))->assertOk();
        $this->post('/peran-aktif/ganti', ['peran' => 'kepala_tu']);
        $this->get(Notifications::getUrl(panel: 'admin'))->assertOk();
        $this->assertSame('kepala_tu', session(ActiveRoles::SESSION_KEY));
    }

    public function test_multi_role_staff_who_never_chose_start_in_their_leadership_role(): void
    {
        $this->signIn($this->staff('front_desk', 'kepala_tu'))->assertRedirect('/cp');

        $this->assertSame('kepala_tu', session(ActiveRoles::SESSION_KEY));
        $this->get(Notifications::getUrl(panel: 'admin'))->assertOk();
    }

    public function test_the_page_asked_for_before_sign_in_opens_after_sign_in(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        $page = Notifications::getUrl(panel: 'admin');

        $this->get($page)->assertRedirect('/login');
        $this->signIn($user)->assertRedirect($page);
    }

    public function test_single_role_staff_and_applicants_go_straight_in(): void
    {
        $this->signIn($this->staff('back_office'))->assertRedirect('/cp');
        $this->assertNull(session(ActiveRoles::PICK_FLAG));
        $this->assertSame('back_office', session(ActiveRoles::SESSION_KEY));
        auth()->logout();

        $applicant = $this->staff('guru', 'umum'); // several roles, none of them staff roles
        $this->signIn($applicant)->assertRedirect('/portal');
        $this->assertNull(session(ActiveRoles::SESSION_KEY));
    }

    public function test_a_new_sign_in_starts_from_the_last_choice_not_the_previous_session(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        $user->forceFill(['active_role_id' => Role::findByName('kepala_tu', 'web')->id])->save();

        $this->withSession([ActiveRoles::SESSION_KEY => 'front_desk', ActiveRoles::PICK_FLAG => true]);
        $this->signIn($user);

        $this->assertSame('kepala_tu', session(ActiveRoles::SESSION_KEY));
        $this->assertNull(session(ActiveRoles::PICK_FLAG));
    }

    public function test_losing_the_extra_role_mid_session_stops_the_prompt(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        $this->signIn($user);
        $user->removeRole('kepala_tu');

        // A real request loads the account (and its roles) afresh.
        $this->actingAs($user->fresh())->get(Notifications::getUrl(panel: 'admin'))->assertOk();
    }
}
