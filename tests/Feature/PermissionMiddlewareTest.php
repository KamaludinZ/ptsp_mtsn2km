<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Middleware izin per route. */
class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Route::middleware(['auth:sanctum', 'izin:backoffice.access|supervision.access'])->get('/api/_uji/bo', fn () => 'ok');
        Route::middleware(['auth:sanctum', 'izin:peran:admin'])->get('/api/_uji/admin', fn () => 'ok');
        Route::middleware(['web', 'auth', 'izin:frontdesk.access'])->get('/_uji/loket', fn () => 'ok');
        Route::middleware(['auth:sanctum', 'izin:izin.tidak.ada'])->get('/api/_uji/tidak-ada', fn () => 'ok');
    }

    private function user(string $role): User
    {
        return User::factory()->create(['user_type' => 'pegawai'])->assignRole(\Spatie\Permission\Models\Role::findByName($role, 'web'));
    }

    public function test_any_listed_permission_or_role_lets_the_request_through_on_the_api(): void
    {
        Sanctum::actingAs($this->user('supervisor'));
        $this->getJson('/api/_uji/bo')->assertOk();

        Sanctum::actingAs($this->user('front_desk'));
        $this->getJson('/api/_uji/bo')->assertForbidden()
            ->assertJsonPath('message', 'Anda tidak memiliki izin untuk ini (perlu: Back Office — buka area back office atau Pengawasan — buka area pengawasan).');

        Sanctum::actingAs($this->user('admin'));
        $this->getJson('/api/_uji/admin')->assertOk();
        Sanctum::actingAs($this->user('back_office'));
        $this->getJson('/api/_uji/admin')->assertForbidden();
    }

    public function test_it_works_on_web_routes_and_for_unknown_permissions(): void
    {
        $this->actingAs($this->user('front_desk'))->get('/_uji/loket')->assertOk();
        $this->actingAs($this->user('back_office'))->get('/_uji/loket')->assertForbidden()->assertSee('Anda tidak memiliki izin untuk ini');

        Sanctum::actingAs($this->user('admin'));
        $this->getJson('/api/_uji/tidak-ada')->assertForbidden(); // not a 500
    }

    public function test_deactivated_accounts_are_refused(): void
    {
        $user = $this->user('back_office');
        $user->forceFill(['is_active' => false])->save();
        Sanctum::actingAs($user);

        $this->getJson('/api/_uji/bo')->assertForbidden();
    }

    public function test_every_api_route_outside_the_public_ones_requires_sign_in(): void
    {
        $open = collect(Route::getRoutes())->filter(fn ($route) => str_starts_with($route->uri(), 'api/'))
            ->reject(fn ($route) => str_starts_with($route->uri(), 'api/publik') || str_starts_with($route->uri(), 'api/_uji')
                || in_array($route->uri(), ['api/auth/masuk', 'api/auth/lupa-kata-sandi', 'api/auth/reset-kata-sandi'], true)
                // Public on purpose (survey form, masked name only) and throttled:
                || ($route->uri() === 'api/check-ticket/{ticketNumber}' && in_array('throttle:30,1', $route->gatherMiddleware(), true)))
            ->reject(fn ($route) => collect($route->gatherMiddleware())->contains(fn ($m) => str_starts_with((string) $m, 'auth:sanctum')))
            ->map(fn ($route) => implode('|', $route->methods()) . ' ' . $route->uri())
            ->values()->all();

        $this->assertSame([], $open, 'API routes without auth:sanctum');
    }
}
