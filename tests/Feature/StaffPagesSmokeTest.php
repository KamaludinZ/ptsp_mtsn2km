<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Opens every parameterless GET page of both panels as each staff role
 * and fails on any server error (500). Catches missing views, routes,
 * columns and PostgreSQL-invalid queries on pages without dedicated tests.
 */
class StaffPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_pages_render_without_server_errors(): void
    {
        \App\Support\RoleAccess::sync();

        Service::factory()->count(2)->create();
        $tickets = Ticket::factory()->count(3)->create();
        $complaints = Complaint::factory()->count(3)->create(['complaint_type' => 'complaint']);
        $whistleblowing = Complaint::factory()->create(['complaint_type' => 'whistleblowing']);
        Visitor::factory()->count(3)->create();

        $users = [];
        foreach (['admin', 'kepala_sekolah', 'back_office', 'front_desk', 'supervisor'] as $role) {
            $user = User::factory()->create(['user_type' => 'pegawai']);
            $user->assignRole(Role::findOrCreate($role));
            $users[$role] = $user;
        }

        $uris = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => in_array('GET', $route->methods(), true))
            ->map(fn ($route) => $route->uri())
            ->filter(fn ($uri) => ! str_contains($uri, '{')
                && Str::startsWith($uri, ['cp', 'portal', 'dashboard', 'profile'])
                && ! Str::contains($uri, ['export', 'download', 'logout']))
            ->merge([
                'cp/tiket/' . $tickets->first()->id,
                'cp/pengaduan/' . $complaints->first()->id,
                'cp/pengaduan/' . $whistleblowing->id,
                'tiket/' . $tickets->first()->id . '/tanda-terima',
            ])
            ->unique()
            ->values();

        $this->assertNotEmpty($uris);

        $failures = [];
        foreach ($users as $role => $user) {
            foreach ($uris as $uri) {
                $this->restoreRedirector();
                $response = $this->actingAs($user)->get('/' . $uri);
                $status = $response->getStatusCode();
                if ($status >= 500) {
                    $failures[] = "{$role} GET /{$uri} -> {$status} " . substr((string) $response->exception?->getMessage(), 0, 160);
                }
            }
        }

        $this->assertSame([], $failures);
    }

    /**
     * Every request in this test shares one application instance. A Livewire
     * page aborted with 403 mid-lifecycle leaves Livewire's redirector bound
     * as 'redirect', which a real (fresh) request never sees.
     */
    private function restoreRedirector(): void
    {
        $this->app->singleton('redirect', function ($app) {
            $redirector = new \Illuminate\Routing\Redirector($app['url']);
            $redirector->setSession($app['session.store']);

            return $redirector;
        });
        $this->app->forgetInstance('redirect');
    }
}
