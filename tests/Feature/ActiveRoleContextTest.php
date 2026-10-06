<?php

namespace Tests\Feature;

use App\Filament\Pages\ChooseActiveRole;
use App\Filament\Pages\Notifications;
use App\Http\Middleware\SetActiveRoleContext;
use App\Models\User;
use App\Support\ActiveRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Middleware SetActiveRoleContext: konteks peran aktif per sesi dan request. */
class ActiveRoleContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // A probe route to read the context the middleware sets.
        Route::middleware(['web', 'auth', 'peran.aktif'])->get('/_uji/peran-aktif', fn () => response()->json([
            'konteks' => ActiveRoles::inContext(),
            'sesi' => session(ActiveRoles::SESSION_KEY),
        ]));
    }

    private function staff(string ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->assignRole(Role::findOrCreate($role, 'web'));
        }

        return $user;
    }

    private function remember(User $user, string $role): void
    {
        $user->forceFill(['active_role_id' => Role::findByName($role, 'web')->id, 'active_role_at' => now()])->save();
    }

    public function test_session_restores_the_last_chosen_role_after_sign_in(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        $this->remember($user, 'front_desk');

        $this->actingAs($user)->get('/_uji/peran-aktif')
            ->assertExactJson(['konteks' => 'front_desk', 'sesi' => 'front_desk']);
    }

    public function test_session_role_wins_over_the_last_choice_of_another_session(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        $this->remember($user, 'front_desk'); // chosen on another device

        $this->actingAs($user)->withSession([ActiveRoles::SESSION_KEY => 'kepala_tu'])
            ->get('/_uji/peran-aktif')
            ->assertJsonPath('konteks', 'kepala_tu');
    }

    public function test_a_role_taken_away_falls_back_and_clears_the_session(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk', 'back_office');
        $user->removeRole('kepala_tu');

        $this->actingAs($user->fresh())->withSession([ActiveRoles::SESSION_KEY => 'kepala_tu'])
            ->get('/_uji/peran-aktif')
            ->assertExactJson(['konteks' => null, 'sesi' => null]);
    }

    public function test_a_single_staff_role_is_the_context_without_choosing(): void
    {
        $this->actingAs($this->staff('back_office'))->get('/_uji/peran-aktif')
            ->assertExactJson(['konteks' => 'back_office', 'sesi' => 'back_office']);
    }

    public function test_switching_updates_the_session_context(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        $this->remember($user, 'kepala_tu');

        $this->actingAs($user)->post('/peran-aktif/ganti', ['peran' => 'front_desk']);

        $this->get('/_uji/peran-aktif')->assertExactJson(['konteks' => 'front_desk', 'sesi' => 'front_desk']);
    }

    public function test_multi_role_staff_must_choose_before_opening_cp_pages(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        $page = Notifications::getUrl();

        $this->actingAs($user)->get($page)->assertRedirect(ChooseActiveRole::getUrl());
        $this->assertSame($page, session('url.intended'));

        // The picker itself opens; so does any page once a role is chosen.
        $this->get(ChooseActiveRole::getUrl())->assertOk();
        $this->post('/peran-aktif/ganti', ['peran' => 'front_desk']);
        $this->get($page)->assertOk();
    }

    public function test_livewire_and_json_requests_are_not_redirected(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');

        $this->actingAs($user)->getJson('/_uji/peran-aktif')->assertOk()->assertJsonPath('konteks', null);
        $this->get(Notifications::getUrl(), ['X-Livewire' => 'true'])->assertOk();
    }

    public function test_applicants_and_guests_pass_through_untouched(): void
    {
        $this->actingAs(User::factory()->create())->get('/_uji/peran-aktif')
            ->assertExactJson(['konteks' => null, 'sesi' => null]);

        $request = \Illuminate\Http\Request::create('/');
        $response = (new SetActiveRoleContext)->handle($request, fn () => response('ok'));
        $this->assertSame('ok', $response->getContent());
        $this->assertFalse($request->attributes->has(ActiveRoles::REQUEST_ATTRIBUTE));
    }

    public function test_api_token_requests_use_the_last_choice(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        $this->remember($user, 'kepala_tu');

        \Laravel\Sanctum\Sanctum::actingAs($user);
        $this->assertSame('kepala_tu', ActiveRoles::inContext($user));
    }
}
