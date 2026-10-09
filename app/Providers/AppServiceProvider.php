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
        // Public-disk files (slides, editor images, visitor photos, logos) as
        // root-relative URLs (/storage/…): always the page's own host and port,
        // whatever APP_URL says, so img-src/connect-src 'self' in the CSP holds.
        config(['filesystems.disks.public.url' => '/storage']);

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
        // TinyMCE rich text fields (App\Filament\Forms\Components\TinyEditor) in the panels.
        \Filament\Support\Facades\FilamentAsset::register([
            \Filament\Support\Assets\Js::make('ptsp-tiny-editor', resource_path('js/tiny-editor.js')),
        ]);

        // Preferensi tampilan (text size, theme mode) in both panels, before Filament's theme script.
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::STYLES_AFTER,
            fn () => new \Illuminate\Support\HtmlString(\App\Support\DisplayPreferences::headHtml(auth()->user())),
        );

        // Rekam jejak aktivitas is audit evidence: entries are only ever added (a database trigger enforces it too).
        \Spatie\Activitylog\Models\Activity::updating(fn () => throw new \LogicException('Rekam jejak aktivitas tidak dapat diubah.'));
        \Spatie\Activitylog\Models\Activity::deleting(fn () => throw new \LogicException('Rekam jejak aktivitas tidak dapat dihapus.'));

        // Ganti akun sementara: anything recorded while an admin uses another account says who really did it.
        \Spatie\Activitylog\Models\Activity::creating(function (\Spatie\Activitylog\Models\Activity $activity) {
            // The role a staff member was working in when they did it (rekam jejak aktivitas).
            $causer = $activity->causer;
            if ($causer instanceof \App\Models\User && ! collect($activity->properties)->has('peran_aktif')
                && ($role = \App\Support\ActiveRoles::actingRoleOf($causer))) {
                $activity->properties = collect($activity->properties)->put('peran_aktif', $role);
            }

            if ($activity->log_name !== 'impersonation' && ($session = \App\Support\Impersonation::current())) {
                $activity->properties = collect($activity->properties)->put('impersonated_by', [
                    'admin_id' => $session['admin']?->getKey(),
                    'admin' => $session['admin_name'],
                    'sesi_id' => $session['log_id'],
                ]);
            }
        });

        // Ganti akun sementara: signing out of a borrowed account closes its log.
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function () {
            if (\App\Support\Impersonation::active()) {
                \App\Support\Impersonation::closeLog(\App\Support\Impersonation::current()['log_id'], 'keluar');
                session()->forget(\App\Support\Impersonation::SESSION_KEY);
            }
        });

        // Ganti akun sementara: a banner on every page of both panels while an admin uses another account.
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::BODY_START,
            fn () => view('components.impersonation-banner'),
        );

        // Pengingat ganti kata sandi: di atas isi halaman kedua panel.
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::CONTENT_START,
            fn () => auth()->check() ? \Illuminate\Support\Facades\Blade::render('@livewire(\App\Livewire\PasswordRotationBanner::class)') : '',
        );

        // E-mail "atur ulang kata sandi" in Indonesian (website and API use the same link).
        \Illuminate\Auth\Notifications\ResetPassword::toMailUsing(function ($user, string $token) {
            $url = url(route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()], false));
            $minutes = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Atur ulang kata sandi - ' . app_brand_name())
                ->greeting('Halo, ' . $user->name . '!')
                ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda.')
                ->action('Atur ulang kata sandi', $url)
                ->line("Tautan ini berlaku selama {$minutes} menit.")
                ->line('Jika Anda tidak meminta ini, abaikan email ini; kata sandi Anda tidak berubah.')
                ->salutation('Salam, ' . app_brand_name());
        });

        // Both panels: Filament remembers the sidebar as open, which on a phone
        // covers the page on the first visit. Start phones with it closed.
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::SCRIPTS_AFTER,
            fn (): string => '<script>document.addEventListener("alpine:initialized",()=>{if(window.innerWidth<1024){window.Alpine.store("sidebar")?.close()}})</script>',
        );

        // Email/WhatsApp settings from Integrasi Notifikasi, without a restart.
        if (! $this->app->runningUnitTests()) {
            \App\Support\IntegrationRuntime::apply();
        }
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Queue\Events\JobProcessing::class,
            fn () => \App\Support\IntegrationRuntime::apply(),
        );

        // Log akses for Monitoring Sistem: who signed in and out, from where.
        // Terakhir masuk, kept on the user (no model events / activity log for this bookkeeping).
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($e) {
            if ($e->user instanceof \App\Models\User) {
                \App\Models\User::whereKey($e->user->getKey())->toBase()->update(['last_login_at' => now()]);
            }
        });

        foreach ([\Illuminate\Auth\Events\Login::class => 'Masuk', \Illuminate\Auth\Events\Logout::class => 'Keluar'] as $event => $label) {
            \Illuminate\Support\Facades\Event::listen($event, function ($e) use ($label) {
                if ($e->user) {
                    activity('access')->causedBy($e->user)
                        ->withProperties(['ip' => request()->ip(), 'agent' => mb_substr((string) request()->userAgent(), 0, 200), 'guard' => $e->guard])
                        ->log($label);
                }
            });
        }

        // Feed the security indicators on Monitoring Sistem.
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Failed::class,
            fn ($event) => \App\Support\SecurityMonitor::recordFailedLogin($event->credentials['email'] ?? null, request()->ip()),
        );
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Lockout::class,
            fn ($event) => \App\Support\SecurityMonitor::recordLockout($event->request->ip()),
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