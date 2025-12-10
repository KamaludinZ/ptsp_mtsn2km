<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\Cache;

class ServiceStats extends BaseWidget
{
    protected static ?int $sort = 3;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('admin_dashboard_service_stats', 120, function () {
            try {
                $totalServices = Service::count();
                $activeServices = Service::where('is_active', true)->count();
                $totalCategories = ServiceCategory::count();
                $onlineServices = Service::where('mode', 'online')->count();
                $offlineServices = Service::where('mode', 'offline')->count();
                $hybridServices = Service::where('mode', 'hybrid')->count();

                $inactiveServices = $totalServices - $activeServices;

                return [
                    // Removed "Total Layanan" - already in DashboardOverview as "Layanan Aktif"

                    Stat::make('Layanan Tidak Aktif', $inactiveServices)
                        ->description('Layanan yang dinonaktifkan')
                        ->descriptionIcon('heroicon-m-x-circle')
                        ->color($inactiveServices > 0 ? 'danger' : 'gray'),

                    Stat::make('Kategori Layanan', $totalCategories)
                        ->description('Jumlah kategori tersedia')
                        ->descriptionIcon('heroicon-m-folder')
                        ->color('info'),

                    Stat::make('Layanan Online', $onlineServices)
                        ->description('Mode: Online')
                        ->descriptionIcon('heroicon-m-globe-alt')
                        ->color('cyan'),

                    Stat::make('Layanan Offline', $offlineServices)
                        ->description('Mode: Offline (On-site)')
                        ->descriptionIcon('heroicon-m-building-office')
                        ->color('orange'),

                    Stat::make('Layanan Hybrid', $hybridServices)
                        ->description('Mode: Online & Offline')
                        ->descriptionIcon('heroicon-m-computer-desktop')
                        ->color('purple'),
                ];
            } catch (\Exception $e) {
                report($e);
                return [
                    Stat::make('Error', 'Database Error')
                        ->description('Could not load service stats')
                        ->color('danger'),
                ];
            }
        });
    }
}