<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Route guards and the access-denied page. */
class AccessDeniedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RoleAccess::sync();
    }

    public function test_users_who_open_the_other_panel_land_on_their_own(): void
    {
        $applicant = User::factory()->create(['user_type' => 'umum']);
        $officer = User::factory()->create(['user_type' => 'pegawai'])->assignRole('back_office');

        $this->actingAs($applicant)->get('/cp')->assertRedirect('/portal');
        $this->actingAs($applicant)->get('/cp/tiket')->assertRedirect('/portal');
        $this->actingAs($officer)->get('/portal')->assertRedirect('/cp');
    }

    public function test_deactivated_accounts_see_the_access_denied_page(): void
    {
        $inactive = User::factory()->create(['user_type' => 'pegawai', 'is_active' => false])->assignRole('back_office');

        $this->actingAs($inactive)->get('/cp')->assertForbidden();
    }

    public function test_access_denied_page_explains_and_offers_a_way_out(): void
    {
        $officer = User::factory()->create(['name' => 'Rina', 'user_type' => 'pegawai'])->assignRole('front_desk');

        // A page in the right panel that this role may not open.
        $this->actingAs($officer)->get(\App\Filament\Resources\UserResource::getUrl())
            ->assertForbidden()
            ->assertSee('Akses ditolak')
            ->assertSee('Rina · Front Desk')
            ->assertSee('Ke dasbor saya')
            ->assertSee('Masuk dengan akun lain')
            ->assertDontSee('This action is unauthorized');
    }

    public function test_guests_are_sent_to_sign_in(): void
    {
        $this->get('/cp')->assertRedirect();
        $this->get('/cp/tiket')->assertRedirect();
    }
}
