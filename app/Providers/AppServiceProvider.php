<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \Filament\Http\Responses\Auth\Contracts\LogoutResponse::class,
            \App\Http\Responses\LogoutResponse::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Request $request): void
    {
        // Both panels: Filament remembers the sidebar as open, which on a phone
        // covers the page on the first visit. Start phones with it closed.
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::SCRIPTS_AFTER,
            fn (): string => '<script>document.addEventListener("alpine:initialized",()=>{if(window.innerWidth<1024){window.Alpine.store("sidebar")?.close()}})</script>',
        );

        if (session()->has('locale')) {
            app()->setLocale(session('locale'));
        }

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Public forms (complaints, whistleblowing, survey, visitor book,
        // contact, registration): stop spam and automated submissions.
        RateLimiter::for('public-forms', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Force HTTPS in production only
        if (app()->environment('production')) {
            \URL::forceScheme('https');

            // Set secure headers for production
            $this->setSecurityHeaders();
        } else {
            // Explicitly force HTTP in local/development
            \URL::forceScheme('http');
        }

        // Share a variable to indicate whether to use Vite or not
        View::composer('*', function ($view) {
            $useVite = (config('app.env') === 'local') && config('assets.mode', 'vite') === 'vite';
            $view->with('useVite', $useVite);

            if (Schema::hasTable('app_settings')) {
                $themePublic = AppSetting::where('key', 'theme_public')->value('value') ?? 'light';
                $themeAdmin = AppSetting::where('key', 'theme_admin')->value('value') ?? 'corporate';
                $view->with('themePublic', $themePublic);
                $view->with('themeAdmin', $themeAdmin);
            }
        });
    }

    /**
     * Set security headers for production environment
     */
    private function setSecurityHeaders(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            // These headers will be set via middleware and .htaccess in production
        }
    }
}