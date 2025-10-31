<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        $user = auth()->user();

        // Different widgets based on user type
        return match($user->user_type ?? 'umum') {
            'guru', 'pegawai' => [
                \App\Filament\Widgets\StaffStatsWidget::class,
                \App\Filament\Widgets\MyTicketsWidget::class,
            ],
            'siswa', 'alumni' => [
                \App\Filament\Widgets\StudentStatsWidget::class,
                \App\Filament\Widgets\MyTicketsWidget::class,
            ],
            'walimurid' => [
                \App\Filament\Widgets\ParentStatsWidget::class,
                \App\Filament\Widgets\MyTicketsWidget::class,
            ],
            'instansi', 'umum' => [
                \App\Filament\Widgets\PublicStatsWidget::class,
                \App\Filament\Widgets\MyTicketsWidget::class,
            ],
            default => [
                \App\Filament\Widgets\OverviewStatsWidget::class,
                \App\Filament\Widgets\RecentTicketsWidget::class,
            ],
        };
    }

    public function getTitle(): string
    {
        $user = auth()->user();

        return match($user->user_type ?? 'umum') {
            'guru' => 'Dashboard Guru',
            'pegawai' => 'Dashboard Pegawai',
            'siswa' => 'Dashboard Siswa',
            'alumni' => 'Dashboard Alumni',
            'walimurid' => 'Dashboard Wali Murid',
            'instansi' => 'Dashboard Instansi',
            'umum' => 'Dashboard Masyarakat',
            default => 'Dashboard',
        };
    }

    public function getHeading(): string
    {
        $user = auth()->user();
        $greeting = $this->getGreeting();

        return "{$greeting}, " . ($user->name ?? 'Pengguna');
    }

    protected function getGreeting(): string
    {
        $hour = now()->format('H');

        if ($hour >= 5 && $hour < 12) {
            return 'Selamat Pagi';
        } elseif ($hour >= 12 && $hour < 15) {
            return 'Selamat Siang';
        } elseif ($hour >= 15 && $hour < 18) {
            return 'Selamat Sore';
        } else {
            return 'Selamat Malam';
        }
    }
}
