<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Helpers\AssetHelper;
use Illuminate\Support\Facades\View;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Request $request): void
    {
        if (session()->has('locale')) {
            app()->setLocale(session('locale'));
        }

        // Register custom Blade directive for intelligent asset loading
        Blade::directive('asset', function ($expression) {
            return "<?php echo App\Helpers\AssetHelper::asset({$expression}); ?>";
        });

        // Register custom Blade directive for CSS assets
        Blade::directive('css', function ($expression) {
            return "<?php echo App\Helpers\AssetHelper::css({$expression}); ?>";
        });

        // Register custom Blade directive for JS assets
        Blade::directive('js', function ($expression) {
            return "<?php echo App\Helpers\AssetHelper::js({$expression}); ?>";
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