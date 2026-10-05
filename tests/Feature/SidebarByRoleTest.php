<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Every sidebar item a role sees opens; items it may not open are not shown. */
class SidebarByRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    /** @return array<int, string> sidebar links of the page */
    private function sidebarLinks(string $html, string $prefix): array
    {
        preg_match_all('/<a[^>]+class="[^"]*fi-sidebar-item-button[^"]*"[^>]*href="([^"]+)"|<a[^>]+href="([^"]+)"[^>]*class="[^"]*fi-sidebar-item-button/', $html, $m);

        return collect(array_merge($m[1], $m[2]))->filter()->map(fn ($url) => html_entity_decode($url))
            ->filter(fn ($url) => str_contains($url, $prefix))->unique()->values()->all();
    }

    public static function roles(): array
    {
        // Staff roles work in /cp; applicant roles are covered by the portal test.
        return collect(User::STAFF_ROLES)->mapWithKeys(fn ($role) => [$role => [$role]])->all();
    }

    /** @dataProvider roles */
    public function test_every_sidebar_item_opens_for_the_role(string $role): void
    {
        $user = User::factory()->create(['user_type' => 'pegawai'])->assignRole($role);
        $this->actingAs($user);

        $home = $this->get('/cp');
        $home->assertOk();
        $links = $this->sidebarLinks($home->getContent(), '/cp');
        $this->assertNotEmpty($links, "{$role} has no sidebar");

        $broken = collect($links)->mapWithKeys(fn ($url) => [$url => $this->get($url)->status()])
            ->filter(fn ($status) => $status >= 400);
        $this->assertSame([], $broken->all(), "{$role}: sidebar links that do not open");
    }

    public function test_admin_only_pages_are_hidden_from_other_staff(): void
    {
        $officer = User::factory()->create(['user_type' => 'pegawai'])->assignRole('back_office');
        $links = $this->sidebarLinks($this->actingAs($officer)->get('/cp')->getContent(), '/cp');

        foreach (['/cp/template-berkas', '/cp/riwayat-notifikasi', '/cp/users', '/cp/roles', '/cp/monitoring-sistem'] as $adminPage) {
            $this->assertFalse(collect($links)->contains(fn ($url) => str_ends_with(parse_url($url, PHP_URL_PATH), $adminPage)), $adminPage);
        }
    }

    public function test_applicants_get_the_portal_menu(): void
    {
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        $links = $this->sidebarLinks($this->actingAs($applicant)->get('/portal')->getContent(), '/portal');

        $this->assertNotEmpty($links);
        foreach ($links as $url) {
            $this->assertLessThan(400, $this->get($url)->status(), $url);
        }
    }
}
