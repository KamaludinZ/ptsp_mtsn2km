<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Every link on each role's dashboard (sidebar menu included) must open:
 * a menu item the role is not allowed to use must not be shown at all.
 */
class DashboardNavigationTest extends TestCase
{
    use RefreshDatabase;

    public static function accounts(): array
    {
        return [
            'admin' => ['ptsp@mtsn2malang.sch.id', '/cp'],
            'kepala_sekolah' => ['kepsek@mtsn2malang.sch.id', '/cp'],
            'kepala_tu' => ['katu@mtsn2malang.sch.id', '/cp'],
            'back_office' => ['staff1@mtsn2malang.sch.id', '/cp'],
            'front_desk' => ['loket1@mtsn2malang.sch.id', '/cp'],
            'supervisor' => ['pengawas@mtsn2malang.sch.id', '/cp'],
            'siswa' => ['ahmad.rizki@student.mtsn2malang.sch.id', '/portal'],
            'umum' => ['budi.santoso@email.com', '/portal'],
        ];
    }

    /** @dataProvider accounts */
    public function test_every_dashboard_link_opens(string $email, string $home): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();

        $user = User::where('email', $email)->firstOrFail();

        $this->actingAs($user)->get('/dashboard')->assertRedirect($home);

        $page = $this->actingAs($user)->get($home)->assertOk();

        // Navigation links only (<a href>), not stylesheets or other assets.
        preg_match_all('/<a\b[^>]*\bhref="([^"#]+)"/i', $page->getContent(), $matches);

        $links = collect($matches[1])
            ->map(fn ($url) => html_entity_decode($url))
            ->filter(fn ($url) => str_starts_with($url, config('app.url')) || str_starts_with($url, '/'))
            ->map(fn ($url) => (parse_url($url, PHP_URL_PATH) ?: '/') . (parse_url($url, PHP_URL_QUERY) ? '?' . parse_url($url, PHP_URL_QUERY) : ''))
            ->reject(fn ($url) => preg_match('#^/(build|css|js|images|storage|favicon|vendor|livewire)#', $url) || str_contains($url, 'logout'))
            ->unique()
            ->values();

        $this->assertNotEmpty($links);

        // A redirect is fine (old URLs forward to their new page) unless it
        // bounces the user to login or e-mail verification.
        $broken = $links->mapWithKeys(function ($url) use ($user) {
            $response = $this->actingAs($user)->get($url);
            $target = parse_url((string) $response->headers->get('Location'), PHP_URL_PATH);

            return [$url => $response->isRedirect() ? "{$response->getStatusCode()} -> {$target}" : $response->getStatusCode()];
        })->filter(fn ($result) => $result !== 200 && (! is_string($result) || preg_match('#-> /(login|email|verify)#', $result)));

        $this->assertSame([], $broken->all(), "Broken links on {$home} for {$email}");
    }
}
