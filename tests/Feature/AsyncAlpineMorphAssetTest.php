<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * resources/js/async-alpine-morph.js: on a phone, "Minta Nomor SK" opened no
 * modal (it sits inside the register table, loaded only once scrolled to) and
 * Livewire updates ran components whose code was not loaded yet. The script is
 * served with the panels' own scripts; the copy in public/ is what goes out.
 */
class AsyncAlpineMorphAssetTest extends TestCase
{
    use RefreshDatabase;

    private const PUBLISHED = 'js/app/ptsp-async-alpine-morph.js';

    public function test_the_panel_pages_load_the_script(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());

        $this->get('/cp/penomoran-sk')->assertOk()->assertSee(self::PUBLISHED, false);
        $this->get('/cp/penomoran-sk/registrasi')->assertOk()->assertSee(self::PUBLISHED, false);
    }

    public function test_the_published_copy_is_the_current_script(): void
    {
        // After changing the script: php artisan filament:assets, and commit public/js/app.
        $this->assertFileEquals(resource_path('js/async-alpine-morph.js'), public_path(self::PUBLISHED));
    }

    public function test_tables_load_at_once_and_waiting_components_stay_ignored(): void
    {
        $script = file_get_contents(resource_path('js/async-alpine-morph.js'));

        $this->assertStringContainsString('[x-data="table"][x-load="visible"]', $script);
        $this->assertStringContainsString("setAttribute('x-load', 'eager')", $script);
        $this->assertStringContainsString("'livewire:navigating'", $script);
        $this->assertStringContainsString("hook('morph.updating'", $script);
    }

    public function test_a_select_taken_off_the_page_gets_empty_lists(): void
    {
        // Escape on Minta Nomor SK: the closed modal's Jenis SK asked for its options again.
        $script = file_get_contents(resource_path('js/async-alpine-morph.js'));

        $this->assertStringContainsString("name !== 'selectFormComponent'", $script);
        $this->assertStringContainsString("['getOptionsUsing', 'getOptionLabelsUsing', 'getSearchResultsUsing']", $script);
        $this->assertStringContainsString('?? []', $script);
    }
}
