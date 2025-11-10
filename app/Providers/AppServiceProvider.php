<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Helpers\AssetHelper;
use Illuminate\Support\Facades\View;

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
    public function boot(): void
    {
        if (session()->has('locale')) {
            app()->setLocale(session('locale'));
        }
        
        // Share a variable to indicate whether to use Vite or not
        View::composer('*', function ($view) {
            $useVite = (config('app.env') === 'local') && config('assets.mode', 'vite') === 'vite';
            $view->with('useVite', $useVite);
        });
    }
}