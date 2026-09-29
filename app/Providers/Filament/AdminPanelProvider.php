<?php

namespace App\Providers\Filament;

use App\Filament\Shared\Pages\EditProfile;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Staff control panel (/cp) for every staff role. Each page and resource
 * decides who may open it, so a role only sees its own menus. Signing in
 * happens on the site's own login page (/login), which has the
 * verification code.
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('cp')
            ->brandName(fn () => app_brand_name())
            ->favicon(asset('images/kemenag-favicon.ico'))
            ->colors([
                'primary' => Color::Green,
                'purple' => Color::Purple,
                'indigo' => Color::Indigo,
                'cyan' => Color::Cyan,
                'orange' => Color::Orange,
            ])
            ->profile(EditProfile::class, isSimple: false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->userMenuItems([
                MenuItem::make()->label('Kembali ke Beranda')->url(fn () => route('home'))->icon('heroicon-o-home'),
            ])
            ->navigationGroups([
                NavigationGroup::make('Pimpinan'),
                NavigationGroup::make('Loket'),
                NavigationGroup::make('Back Office'),
                NavigationGroup::make('Pengawasan'),
                NavigationGroup::make('Manajemen Layanan')->collapsed(),
                NavigationGroup::make('Manajemen Survey')->collapsed(),
                NavigationGroup::make('Manajemen Pengguna')->collapsed(),
                NavigationGroup::make('Manajemen Pengumuman')->collapsed(),
                NavigationGroup::make('Manajemen Sistem')->collapsed(),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('screen-2xl')
            ->spa()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
