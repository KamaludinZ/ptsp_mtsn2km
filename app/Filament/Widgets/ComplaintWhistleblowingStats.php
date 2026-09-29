<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Complaint;
use Illuminate\Support\Facades\Cache;

class ComplaintWhistleblowingStats extends BaseWidget
{
    protected static bool $isDiscovered = false;

    /** Part of the administrator's dashboard. */
    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    protected static ?int $sort = 51;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('admin_dashboard_complaint_whistleblowing_stats', 120, function () {
            try {
                // Whistleblowing reports live in the complaints table
                // (complaint_type = whistleblowing); statuses are
                // submitted -> in_review/in_progress -> resolved/closed.
                $count = fn (array $types, array $statuses) => Complaint::whereIn('complaint_type', $types)
                    ->whereIn('status', $statuses)
                    ->count();

                $dumas = ['complaint', 'suggestion'];

                return [
                    Stat::make('Pengaduan Diproses', $count($dumas, ['in_review', 'in_progress']))
                        ->description('Sedang ditindaklanjuti')
                        ->descriptionIcon('heroicon-m-arrow-path')
                        ->color('warning'),

                    Stat::make('Pengaduan Selesai', $count($dumas, ['resolved', 'closed']))
                        ->description('Pengaduan ditangani')
                        ->descriptionIcon('heroicon-m-check-circle')
                        ->color('success'),

                    Stat::make('WBS Diproses', $count(['whistleblowing'], ['in_review', 'in_progress']))
                        ->description('Sedang diinvestigasi')
                        ->descriptionIcon('heroicon-m-arrow-path')
                        ->color('warning'),

                    Stat::make('WBS Selesai', $count(['whistleblowing'], ['resolved', 'closed']))
                        ->description('Laporan ditangani')
                        ->descriptionIcon('heroicon-m-check-circle')
                        ->color('success'),
                ];
            } catch (\Throwable $e) {
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