<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Guide;
use App\Models\User;
use App\Support\ActiveRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Tombol Panduan di header /cp dan halaman Panduan Menu sesuai peran aktif. */
class PanelGuideTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    public function test_header_has_a_guide_button(): void
    {
        $this->actingAs(User::role('admin')->firstOrFail())
            ->get(Dashboard::getUrl())
            ->assertOk()
            ->assertSee(Guide::getUrl(), false)
            ->assertSee('Panduan');
    }

    public function test_guide_lists_only_the_menus_of_the_active_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findByName('front_desk', 'web'), Role::findByName('kepala_tu', 'web'));

        $this->actingAs($user)->withSession([ActiveRoles::SESSION_KEY => 'front_desk'])
            ->get(Guide::getUrl())
            ->assertOk()
            ->assertSee('id="panduan-register-service"', false)
            ->assertSee('id="panduan-visitor-resource"', false)
            ->assertDontSee('id="panduan-approvals"', false)
            ->assertDontSee('id="panduan-notification-integrations"', false);

        $this->withSession([ActiveRoles::SESSION_KEY => 'kepala_tu'])
            ->get(Guide::getUrl())
            ->assertOk()
            ->assertSee('id="panduan-approvals"', false)
            ->assertSee('Diteruskan kepada')
            ->assertDontSee('id="panduan-notification-integrations"', false);
    }

    public function test_admin_guide_covers_system_menus(): void
    {
        $this->actingAs(User::role('admin')->firstOrFail())
            ->get(Guide::getUrl())
            ->assertOk()
            ->assertSee('id="panduan-notification-integrations"', false)
            ->assertSee('id="panduan-service-resource"', false);
    }

    public function test_every_menu_of_the_staff_panel_has_written_guidance(): void
    {
        $content = \App\Support\PanelGuide::content('admin');
        $panel = \Filament\Facades\Filament::getPanel('admin');

        $missing = collect([...$panel->getResources(), ...$panel->getPages()])
            ->reject(fn (string $class) => in_array($class, [Guide::class, \App\Filament\Pages\ChooseActiveRole::class], true))
            ->reject(fn (string $class) => array_key_exists($class, $content))
            ->values()
            ->all();

        $this->assertSame([], $missing);
    }
}
