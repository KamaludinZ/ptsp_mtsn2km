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
 * Opens every parameterless GET page of the staff areas as each staff role
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
                && Str::startsWith($uri, ['admin', 'backoffice', 'frontdesk', 'supervision', 'pimpinan', 'portal', 'profile'])
                && ! Str::contains($uri, ['export', 'download', 'logout']))
            ->merge([
                'backoffice/tickets/' . $tickets->first()->ticket_number,
                'admin/complaints/' . $complaints->first()->id,
                'admin/whistleblowing/' . $whistleblowing->id,
            ])
            ->unique()
            ->values();

        $this->assertNotEmpty($uris);

        $failures = [];
        foreach ($users as $role => $user) {
            foreach ($uris as $uri) {
                $status = $this->actingAs($user)->get('/' . $uri)->getStatusCode();
                if ($status >= 500) {
                    $failures[] = "{$role} GET /{$uri} -> {$status}";
                }
            }
        }

        $this->assertSame([], $failures);
    }
}
