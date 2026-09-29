<?php

namespace App\Providers\Filament;

use App\Filament\Shared\Pages\EditProfile;
use App\Http\Middleware\CheckEmailVerification;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
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
 * Applicant portal (/portal): guru, pegawai, siswa, wali murid, alumni,
 * instansi and umum follow their requests, download results and apply for
 * services. Applicants whose e-mail is not verified are sent to verify it.
 */
class PortalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('portal')
            ->path('portal')
            ->brandName(fn () => app_brand_name())
            ->favicon(asset('images/kemenag-favicon.ico'))
            ->colors([
                'primary' => Color::Green,
            ])
            ->profile(EditProfile::class, isSimple: false)
            ->topNavigation()
            ->discoverResources(in: app_path('Filament/Portal/Resources'), for: 'App\\Filament\\Portal\\Resources')
            ->discoverPages(in: app_path('Filament/Portal/Pages'), for: 'App\\Filament\\Portal\\Pages')
            ->discoverWidgets(in: app_path('Filament/Portal/Widgets'), for: 'App\\Filament\\Portal\\Widgets')
            ->navigationItems([
                NavigationItem::make('Katalog Layanan')
                    ->icon('heroicon-o-squares-2x2')
                    ->url(fn () => route('onlineportal.service.catalog'))
                    ->sort(50),
            ])
            ->userMenuItems([
                MenuItem::make()->label('Kembali ke Beranda')->url(fn () => route('home'))->icon('heroicon-o-home'),
            ])
            ->maxContentWidth('7xl')
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
                CheckEmailVerification::class,
            ]);
    }
}
