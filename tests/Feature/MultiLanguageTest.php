<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class MultiLanguageTest extends TestCase
{
    public function test_default_locale_is_indonesian()
    {
        // Without setting a locale, the default should be Indonesian
        $this->assertEquals('id', config('app.locale'));
    }

    public function test_supported_locales_config_exists()
    {
        $supportedLocales = config('app.supported_locales');
        $this->assertIsArray($supportedLocales);
        $this->assertContains('en', $supportedLocales);
        $this->assertContains('id', $supportedLocales);
        $this->assertContains('ar', $supportedLocales);
    }
    
    public function test_locale_route_works()
    {
        // Test the locale route with a referer to avoid redirect issues
        $response = $this->withHeaders(['Referer' => '/'])->get('/set-locale/id');
        
        $response->assertStatus(302);
        
        // Check that the locale was saved to session
        $this->assertEquals('id', session('locale'));
    }
    
    public function test_all_supported_locales_work()
    {
        $supportedLocales = config('app.supported_locales');
        
        foreach ($supportedLocales as $locale) {
            $response = $this->withHeaders(['Referer' => '/'])->get("/set-locale/{$locale}");
            $response->assertStatus(302);
            $this->assertEquals($locale, session('locale'));
        }
    }
    
    public function test_unsupported_locale_is_ignored()
    {
        $response = $this->withHeaders(['Referer' => '/'])->get('/set-locale/fr');
        $response->assertStatus(302);
        
        // Since 'fr' is not in supported locales, session locale should not be set to 'fr'
        // It might remain as the default or previous value
        $this->assertNotEquals('fr', session('locale'));
    }

    public function test_translation_works()
    {
        // Test that translations work for different locales
        $this->get('/set-locale/id')->withHeaders(['Referer' => '/']);
        $this->assertEquals('Beranda', __('Home')); // Should be in Indonesian
        
        $this->get('/set-locale/en')->withHeaders(['Referer' => '/']);
        $this->assertEquals('Home', __('Home')); // Should be in English
    }
}