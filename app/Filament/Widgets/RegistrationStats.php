<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RegistrationStats extends BaseWidget
{
    protected static ?int $sort = 22;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('admin_dashboard_registration_stats', 120, function () {
            try {
                $totalUsers = User::count();
                $verifiedUsers = User::where('email_verified_at', '!=', null)->count();
                $unverifiedUsers = $totalUsers - $verifiedUsers;
                $usersThisMonth = User::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count();
                $verifiedThisMonth = User::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->where('email_verified_at', '!=', null)
                    ->count();

                // Removed: Total Registrasi (already in DashboardOverview as Total Pengguna)
                return [
                    Stat::make('Terverifikasi', $verifiedUsers)
                        ->description('Pengguna Terverifikasi')
                        ->descriptionIcon('heroicon-m-check-badge')
                        ->color('success'),

                    Stat::make('Belum Verifikasi', $unverifiedUsers)
                        ->description('Menunggu Verifikasi')
                        ->descriptionIcon('heroicon-m-clock')
                        ->color($unverifiedUsers > 10 ? 'warning' : 'gray'),

                    Stat::make('Registrasi Bulan Ini', $usersThisMonth)
                        ->description('Pendaftaran Bulan ' . now()->format('F'))
                        ->descriptionIcon('heroicon-m-calendar-days')
                        ->color('primary'),

                    Stat::make('Verifikasi Bulan Ini', $verifiedThisMonth)
                        ->description('Verifikasi Bulan ' . now()->format('F'))
                        ->descriptionIcon('heroicon-m-check-circle')
                        ->color('emerald'),
                ];
            } catch (\Exception $e) {
                report($e);
                return [
                    Stat::make('Error', 'Database Error')
                        ->description('Could not load registration stats')
                        ->color('danger'),
                ];
            }
        });
    }
}