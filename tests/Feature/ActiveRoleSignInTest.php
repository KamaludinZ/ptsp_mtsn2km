<?php

namespace Tests\Feature;

use App\Filament\Pages\ChooseActiveRole;
use App\Filament\Pages\Notifications;
use App\Models\User;
use App\Support\ActiveRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Masuk ke /cp: petugas multi-peran memilih peran aktif dulu. */
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

    public function test_multi_role_staff_land_on_the_role_picker(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        // A choice from an earlier session is remembered, but still asked again.
        $user->forceFill(['active_role_id' => Role::findByName('front_desk', 'web')->id])->save();

        $this->signIn($user)->assertRedirect(ChooseActiveRole::getUrl(panel: 'admin'));
        $this->assertTrue(session(ActiveRoles::PICK_FLAG));

        // Every /cp page leads back to the picker until a role is chosen...
        $this->get(Notifications::getUrl(panel: 'admin'))->assertRedirect(ChooseActiveRole::getUrl(panel: 'admin'));
        $this->get(ChooseActiveRole::getUrl(panel: 'admin'))->assertOk();

        // ...and after choosing, /cp opens with that role as the context.
        $this->post('/peran-aktif/ganti', ['peran' => 'kepala_tu']);
        $this->assertNull(session(ActiveRoles::PICK_FLAG));
        $this->get(Notifications::getUrl(panel: 'admin'))->assertOk();
        $this->assertSame('kepala_tu', session(ActiveRoles::SESSION_KEY));
    }

    public function test_the_page_asked_for_before_sign_in_is_kept_for_after_choosing(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        $page = Notifications::getUrl(panel: 'admin');

        $this->get($page)->assertRedirect('/login');
        $this->signIn($user)->assertRedirect(ChooseActiveRole::getUrl(panel: 'admin'));

        $this->assertSame($page, session('url.intended'));
    }

    public function test_single_role_staff_and_applicants_go_straight_in(): void
    {
        $this->signIn($this->staff('back_office'))->assertRedirect('/cp');
        $this->assertNull(session(ActiveRoles::PICK_FLAG));
        $this->assertNull(session(ActiveRoles::SESSION_KEY));
        auth()->logout();

        $applicant = $this->staff('guru', 'umum'); // several roles, none of them staff roles
        $this->signIn($applicant)->assertRedirect('/portal');
        $this->assertNull(session(ActiveRoles::PICK_FLAG));
    }

    public function test_a_new_sign_in_forgets_the_role_of_the_previous_session(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');

        $this->withSession([ActiveRoles::SESSION_KEY => 'front_desk']);
        $this->signIn($user);

        $this->assertNull(session(ActiveRoles::SESSION_KEY));
        $this->assertTrue(session(ActiveRoles::PICK_FLAG));
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
