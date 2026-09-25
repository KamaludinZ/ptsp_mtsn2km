<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicChromeTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_has_no_language_switcher_and_brand_is_admin_editable(): void
    {
        $this->get('/')->assertOk()
            ->assertDontSee('changeLanguage')
            ->assertDontSee('set-locale')
            ->assertSee('PTSP MTsN 2 KOTA MALANG');

        AppSetting::updateOrCreate(['key' => 'app_name'], ['value' => 'Nama Baru Uji', 'type' => 'text', 'category' => 'branding']);

        // Settings are mapped into config at boot; a real request boots fresh.
        (new \App\Providers\AppSettingsServiceProvider($this->app))->boot();

        $this->get('/')->assertOk()->assertSee('Nama Baru Uji');
    }

    public function test_footer_shows_visitor_statistics_and_counts_unique_visitors_per_day(): void
    {
        Cache::flush();
        $headers = ['User-Agent' => 'Mozilla/5.0 (X11; Linux) Firefox/130.0'];

        $this->withHeaders($headers)->get('/')->assertOk();
        $this->withHeaders($headers)->get('/')->assertOk();

        $this->assertSame(1, DB::table('site_visits')->count());
        $this->get('/')->assertSee('Statistik Pengunjung');
    }

    public function test_bots_are_not_counted(): void
    {
        $this->withHeaders(['User-Agent' => 'Googlebot/2.1'])->get('/')->assertOk();
        $this->assertSame(0, DB::table('site_visits')->count());
    }
}
