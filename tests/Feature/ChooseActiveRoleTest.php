<?php

namespace Tests\Feature;

use App\Filament\Pages\ChooseActiveRole;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Notifications;
use App\Livewire\ActiveRoleSwitcher;
use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\ActiveRoleToast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Halaman pemilihan peran aktif, penanda, menu ganti peran di header, dan toast perpindahan. */
class ChooseActiveRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    /** Kepala TU who also serves at the counter and in tata usaha, working as Kepala TU. */
    private function multiRole(): User
    {
        $user = User::factory()->create();
        $user->assignRole(['kepala_tu', 'front_desk', 'tata_usaha']);
        $user->forceFill(['active_role_id' => Role::findByName('kepala_tu', 'web')->id, 'active_role_at' => now()->subHour()])->save();

        return $user->fresh();
    }

    public function test_staff_sees_their_own_role_cards_with_the_active_one_marked(): void
    {
        $this->actingAs($this->multiRole())
            ->get(ChooseActiveRole::getUrl())
            ->assertOk()
            ->assertSeeInOrder(['Front Desk', 'Kepala Tata Usaha', 'Sedang aktif', 'Pilihan terakhir', 'Tata Usaha'])
            ->assertSee('Gunakan peran ini')
            ->assertDontSee('Administrator');
    }

    public function test_choosing_a_role_saves_it_and_returns_to_the_dashboard(): void
    {
        $user = $this->multiRole();
        $this->actingAs($user);

        Livewire::test(ChooseActiveRole::class)
            ->call('choose', 'front_desk')
            ->assertNotified('Peran aktif: Front Desk')
            ->assertRedirect(Dashboard::getUrl());

        $this->assertSame('front_desk', $user->fresh()->activeRole->name);
    }

    public function test_choosing_goes_on_to_the_page_that_was_asked_for(): void
    {
        $this->actingAs($this->multiRole());
        session(['url.intended' => Notifications::getUrl()]);

        Livewire::test(ChooseActiveRole::class)
            ->call('choose', 'tata_usaha')
            ->assertRedirect(Notifications::getUrl());
    }

    public function test_unknown_role_is_refused(): void
    {
        $this->actingAs($this->multiRole());

        Livewire::test(ChooseActiveRole::class)
            ->call('choose', 'admin')
            ->assertNotified('Peran tidak tersedia untuk akun ini')
            ->assertNoRedirect();
    }

    public function test_applicants_cannot_open_the_picker(): void
    {
        $this->assertFalse(User::where('email', 'budi.santoso@email.com')->firstOrFail()->isStaff());

        $this->actingAs(User::where('email', 'budi.santoso@email.com')->firstOrFail())
            ->get(ChooseActiveRole::getUrl())
            ->assertRedirect();
    }

    public function test_cp_header_shows_the_active_role_linking_to_the_picker(): void
    {
        $this->actingAs($this->multiRole())
            ->withSession([ActiveRoles::SESSION_KEY => 'front_desk'])
            ->get(Dashboard::getUrl())
            ->assertOk()
            ->assertSee('data-active-role="front_desk"', false)
            ->assertSeeInOrder(['Peran aktif', 'Front Desk'])
            ->assertSee(ChooseActiveRole::getUrl(), false);
    }

    public function test_single_role_staff_see_their_role_in_the_header(): void
    {
        $this->actingAs(User::role('admin')->firstOrFail())
            ->get(Dashboard::getUrl())
            ->assertOk()
            ->assertSee('data-active-role="admin"', false);
    }

    public function test_header_menu_lists_every_role_to_switch_to(): void
    {
        $this->actingAs($this->multiRole());

        Livewire::test(ActiveRoleSwitcher::class)
            ->assertSeeInOrder(['Ganti peran', 'Front Desk', 'Kepala Tata Usaha · aktif', 'Tata Usaha', 'Lihat semua peran'])
            ->assertSeeHtml("switchTo('front_desk')")
            ->assertDontSeeHtml("switchTo('kepala_tu')");
    }

    public function test_quick_switch_stays_on_the_current_page(): void
    {
        $user = $this->multiRole();
        $this->actingAs($user);
        $page = Notifications::getUrl();

        Livewire::test(ActiveRoleSwitcher::class)
            ->set('returnUrl', $page)
            ->call('switchTo', 'tata_usaha')
            ->assertNotified('Peran aktif: Tata Usaha')
            ->assertRedirect($page);

        $this->assertSame('tata_usaha', $user->fresh()->activeRole->name);
    }

    public function test_quick_switch_refuses_a_role_the_user_does_not_hold(): void
    {
        $this->actingAs($this->multiRole());

        Livewire::test(ActiveRoleSwitcher::class)
            ->call('switchTo', 'siswa')
            ->assertNotified('Peran tidak tersedia untuk akun ini')
            ->assertNoRedirect();
    }

    public function test_switch_toast_names_both_roles_and_offers_to_switch_back(): void
    {
        $this->actingAs($this->multiRole());

        Livewire::test(ActiveRoleSwitcher::class)->call('switchTo', 'front_desk');

        $toast = collect(session('filament.notifications'))->last();
        $this->assertSame('Peran aktif: Front Desk', $toast['title']);
        $this->assertSame('success', $toast['status']);
        $this->assertStringContainsString('Berpindah dari Kepala Tata Usaha', $toast['body']);
        $this->assertSame('Kembali ke Kepala Tata Usaha', $toast['actions'][0]['label']);
        $this->assertSame(ActiveRoleToast::UNDO_EVENT, $toast['actions'][0]['event']);
        $this->assertSame(['role' => 'kepala_tu'], $toast['actions'][0]['eventData']);
    }

    public function test_switch_back_event_is_handled_by_the_header_menu(): void
    {
        $user = $this->multiRole();
        $this->actingAs($user);

        Livewire::test(ActiveRoleSwitcher::class)
            ->call('switchTo', 'front_desk')
            ->dispatch(ActiveRoleToast::UNDO_EVENT, role: 'kepala_tu')
            ->assertNotified('Peran aktif: Kepala Tata Usaha');

        $this->assertSame('kepala_tu', $user->fresh()->activeRole->name);
    }

    public function test_continuing_with_the_active_role_shows_no_switch_toast(): void
    {
        $this->actingAs($this->multiRole());

        Livewire::test(ChooseActiveRole::class)
            ->call('choose', 'kepala_tu')
            ->assertNotified('Anda sudah memakai peran Kepala Tata Usaha')
            ->assertRedirect(Dashboard::getUrl());
    }

    public function test_applicant_portal_has_no_active_role_marker(): void
    {
        $this->actingAs(User::where('email', 'budi.santoso@email.com')->firstOrFail())
            ->get('/portal')
            ->assertOk()
            ->assertDontSee('data-active-role', false);
    }

    public function test_marker_stays_out_of_the_portal_after_the_cp_panel_booted(): void
    {
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();

        $this->actingAs($applicant)->get(Dashboard::getUrl(panel: 'admin'))->assertRedirect();
        $this->get('/portal')->assertOk()->assertDontSee('data-active-role', false);
    }

    public function test_page_is_not_in_the_sidebar(): void
    {
        $this->assertFalse(ChooseActiveRole::shouldRegisterNavigation());
    }
}
