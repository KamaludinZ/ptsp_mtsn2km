<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Complaint;
use App\Models\Whistleblowing;
use Illuminate\Support\Facades\Cache;

class ComplaintWhistleblowingStats extends BaseWidget
{
    protected static ?int $sort = 51;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('admin_dashboard_complaint_whistleblowing_stats', 120, function () {
            try {
                $totalComplaints = Complaint::count();
                $pendingComplaints = Complaint::where('status', 'pending')
                    ->orWhere('status', 'investigating')
                    ->count();
                $resolvedComplaints = Complaint::where('status', 'resolved')->count();
                
                $totalWhistleblowing = Whistleblowing::count();
                $pendingWhistleblowing = Whistleblowing::where('status', 'pending')
                    ->orWhere('status', 'investigating')
                    ->count();
                $resolvedWhistleblowing = Whistleblowing::where('status', 'resolved')->count();

                // Removed: Total Pengaduan, Pengaduan Pending, WBS Pending (already in DashboardOverview)
                return [
                    Stat::make('Pengaduan Selesai', $resolvedComplaints)
                        ->description('Pengaduan Ditangani')
                        ->descriptionIcon('heroicon-m-check-circle')
                        ->color('success'),

                    Stat::make('Pengaduan Ditolak', Complaint::where('status', 'rejected')->count())
                        ->description('Pengaduan Ditolak')
                        ->descriptionIcon('heroicon-m-x-circle')
                        ->color('danger'),

                    Stat::make('WBS Selesai', $resolvedWhistleblowing)
                        ->description('WBS Ditangani')
                        ->descriptionIcon('heroicon-m-check-circle')
                        ->color('emerald'),

                    Stat::make('WBS Ditolak', Whistleblowing::where('status', 'rejected')->count())
                        ->description('WBS Ditolak')
                        ->descriptionIcon('heroicon-m-x-circle')
                        ->color('orange'),
                ];
            } catch (\Exception $e) {
                report($e);
                return [
                    Stat::make('Error', 'Database Error')
                        ->description('Could not load complaint/whistleblowing stats')
                        ->color('danger'),
                ];
            }
        });
    }
}