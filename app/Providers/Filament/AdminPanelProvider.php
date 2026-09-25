<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('cp')
            ->login()
            ->brandName(fn () => app_brand_name())
            ->colors([
                'primary' => Color::Green,
                'purple' => Color::Purple,
                'indigo' => Color::Indigo,
                'cyan' => Color::Cyan,
                'orange' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->navigationItems($this->workspaceLinks())
            ->userMenuItems([
                MenuItem::make()->label('Kembali ke Beranda')->url(fn () => route('home'))->icon('heroicon-o-home'),
            ])
            ->navigationGroups([
                'Pimpinan',
                'Loket',
                'Back Office',
                'Pengawasan',
                'Manajemen Pengguna',
                'Manajemen Layanan',
                'Manajemen Tiket',
                'Manajemen Pengunjung',
                'Manajemen Pengaduan',
                'Manajemen Survey',
                'Manajemen Keamanan',
                'Manajemen Pengumuman',
                'Manajemen Sistem',
            ])
            ->sidebarCollapsibleOnDesktop()
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

    /**
     * Workspaces that live outside the panel (leadership, front desk, back
     * office, supervision, security). They are the same menus the role
     * dashboards show, each visible only when the account may open it.
     *
     * @return array<int, NavigationItem>
     */
    private function workspaceLinks(): array
    {
        // [group, label, icon, route name, active pattern(s), ability check]
        $links = [
            ['Pimpinan', 'Dashboard Eksekutif', 'heroicon-o-chart-bar', 'leadership.dashboard', ['leadership.dashboard'], 'leader'],
            ['Pimpinan', 'Persetujuan', 'heroicon-o-clipboard-document-check', 'leadership.approvals', ['leadership.approvals'], 'leader'],

            ['Loket', 'Dashboard Loket', 'heroicon-o-computer-desktop', 'frontdesk.dashboard', ['frontdesk.dashboard'], 'frontdesk.access'],
            ['Loket', 'Triage Pengunjung', 'heroicon-o-user-plus', 'frontdesk.triage', ['frontdesk.triage'], 'frontdesk.access'],
            ['Loket', 'Registrasi Layanan', 'heroicon-o-document-plus', 'frontdesk.service.application', ['frontdesk.service.*'], 'frontdesk.access'],
            ['Loket', 'Buku Tamu', 'heroicon-o-book-open', 'frontdesk.visitor-book', ['frontdesk.visitor-book'], 'frontdesk.access'],
            ['Loket', 'Tamu Aktif', 'heroicon-o-users', 'frontdesk.active-visitors', ['frontdesk.active-visitors'], 'frontdesk.access'],

            ['Back Office', 'Dashboard Back Office', 'heroicon-o-briefcase', 'backoffice.dashboard', ['backoffice.dashboard'], 'backoffice.access'],
            ['Back Office', 'Antrian Tugas', 'heroicon-o-inbox', 'backoffice.tickets.queue', ['backoffice.tickets.queue'], 'backoffice.access'],
            ['Back Office', 'Tugas Saya', 'heroicon-o-clipboard-document-list', 'backoffice.tickets.my', ['backoffice.tickets.my'], 'backoffice.access'],
            ['Back Office', 'Semua Tiket', 'heroicon-o-queue-list', 'backoffice.tickets.all', ['backoffice.tickets.all'], 'backoffice.access'],
            ['Back Office', 'Cari Tiket', 'heroicon-o-magnifying-glass', 'backoffice.tickets.search', ['backoffice.tickets.search'], 'backoffice.access'],
            ['Back Office', 'Laporan Layanan', 'heroicon-o-document-chart-bar', 'backoffice.reports', ['backoffice.reports'], 'backoffice.access'],

            ['Pengawasan', 'Dashboard Pengawasan', 'heroicon-o-eye', 'supervision.management', ['supervision.management'], 'supervision.access'],
            ['Pengawasan', 'Kinerja Pelayanan', 'heroicon-o-chart-pie', 'supervision.performance', ['supervision.performance'], 'supervision.access'],
            ['Pengawasan', 'Tindak Lanjut Pengaduan', 'heroicon-o-chat-bubble-left-right', 'admin.complaints.index', ['admin.complaints.*'], 'complaint'],
            ['Pengawasan', 'Whistleblowing', 'heroicon-o-shield-exclamation', 'admin.whistleblowing.index', ['admin.whistleblowing.*'], 'complaint'],
            ['Pengawasan', 'Laporan SKM & SPAK', 'heroicon-o-presentation-chart-line', 'admin.performance.report', ['admin.performance.report', 'admin.skm.report', 'admin.spak.report'], 'complaint'],

            ['Manajemen Survey', 'Edisi & Arsip Survei', 'heroicon-o-archive-box', 'admin.survey.management', ['admin.survey.*'], 'admin'],
            ['Manajemen Keamanan', 'Keamanan Sistem', 'heroicon-o-shield-check', 'admin.security.dashboard', ['admin.security.*'], 'admin'],
        ];

        $allowed = function (?\App\Models\User $user, string $ability): bool {
            if (! $user) {
                return false;
            }

            return match ($ability) {
                'leader' => $user->hasAnyRole(\App\Support\RoleAccess::LEADERSHIP),
                'complaint' => $user->hasAnyRole(\App\Support\RoleAccess::COMPLAINT_HANDLERS),
                'admin' => $user->hasRole('admin'),
                default => $user->can($ability),
            };
        };

        return collect($links)->map(fn (array $l, int $i) => NavigationItem::make($l[1])
            ->group($l[0])
            ->icon($l[2])
            ->url(fn () => route($l[3]))
            ->isActiveWhen(fn () => request()->routeIs(...$l[4]))
            ->visible(fn () => $allowed(auth()->user(), $l[5]))
            ->sort($i + 1)
        )->all();
    }
}
