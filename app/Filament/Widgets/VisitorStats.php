<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Visitor;
use Illuminate\Support\Facades\Cache;

class VisitorStats extends BaseWidget
{
    protected static ?int $sort = 41;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('admin_dashboard_visitor_stats', 120, function () {
            try {
                $visitorsToday = Visitor::whereDate('created_at', now()->today())->count();
                $totalVisitors = Visitor::count();
                $activeVisitors = Visitor::where('status', 'active')->count();
                $completedVisitors = Visitor::where('status', 'completed')->count();
                $cancelledVisitors = Visitor::where('status', 'cancelled')->count();

                // Removed "Total Pengunjung" - already in DashboardOverview
                $visitorsThisWeek = Visitor::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count();

                $visitorsThisMonth = Visitor::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count();

                return [
                    // Removed "Total Pengunjung" and "Pengunjung Hari Ini" (already in overview)

                    Stat::make('Minggu Ini', $visitorsThisWeek)
                        ->description('Pengunjung minggu ini')
                        ->descriptionIcon('heroicon-m-calendar-days')
                        ->color('primary'),

                    Stat::make('Bulan Ini', $visitorsThisMonth)
                        ->description('Pengunjung bulan ' . now()->format('F'))
                        ->descriptionIcon('heroicon-m-calendar')
                        ->color('success'),

                    Stat::make('Sedang Berkunjung', $activeVisitors)
                        ->description('Pengunjung yang masih di lokasi')
                        ->descriptionIcon('heroicon-m-user')
                        ->color($activeVisitors > 0 ? 'warning' : 'gray'),

                    Stat::make('Selesai Berkunjung', $completedVisitors)
                        ->description('Kunjungan telah selesai')
                        ->descriptionIcon('heroicon-m-check-circle')
                        ->color('info'),

                    Stat::make('Dibatalkan', $cancelledVisitors)
                        ->description('Kunjungan dibatalkan')
                        ->descriptionIcon('heroicon-m-x-circle')
                        ->color($cancelledVisitors > 5 ? 'danger' : 'gray'),
                ];
            } catch (\Exception $e) {
                report($e);
                return [
                    Stat::make('Error', 'Database Error')
                        ->description('Could not load visitor stats')
                        ->color('danger'),
                ];
            }
        });
    }
}