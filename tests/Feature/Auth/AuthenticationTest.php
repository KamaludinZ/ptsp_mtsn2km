<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['captcha_value' => 'ABCDE'])->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'captcha' => 'ABCDE',
        ]);

        $this->assertAuthenticated();
        // A plain applicant lands on the applicant portal
        $response->assertRedirect('/portal/dashboard');
    }

    public function test_login_redirects_each_role_to_its_own_dashboard(): void
    {
        \App\Support\RoleAccess::sync();

        foreach (['admin' => '/cp', 'kepala_sekolah' => '/pimpinan', 'back_office' => '/backoffice/dashboard', 'front_desk' => '/frontdesk/dashboard'] as $role => $url) {
            $user = User::factory()->create();
            $user->assignRole($role);

            $this->withSession(['captcha_value' => 'ABCDE'])->post('/login', [
                'email' => $user->email,
                'password' => 'password',
                'captcha' => 'ABCDE',
            ])->assertRedirect($url);

            auth()->logout();
        }
    }

    public function test_admin_panel_sidebar_lists_workspace_menus(): void
    {
        \App\Support\RoleAccess::sync();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get('/cp')->assertOk()
            ->assertSee('Dashboard Loket')
            ->assertSee('Antrian Tugas')
            ->assertSee('Persetujuan')
            ->assertSee('Tindak Lanjut Pengaduan')
            ->assertSee('Keamanan Sistem');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
