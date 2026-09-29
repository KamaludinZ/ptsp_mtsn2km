<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class TicketStats extends BaseWidget
{
    protected static bool $isDiscovered = false;

    /** Part of the administrator's dashboard. */
    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    protected static ?int $sort = 32;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('admin_dashboard_ticket_stats', 120, function () {
            try {
                $totalTickets = Ticket::count();
                $ticketsThisMonth = Ticket::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count();
                $pendingTickets = Ticket::where('status', 'submitted')
                    ->orWhere('status', 'verified')
                    ->orWhere('status', 'in_process')
                    ->orWhere('status', 'pending_approval')
                    ->count();
                $completedTickets = Ticket::where('status', 'completed')
                    ->orWhere('status', 'approved')
                    ->count();
                $cancelledTickets = Ticket::where('status', 'cancelled')
                    ->orWhere('status', 'rejected')
                    ->count();

                // Removed "Tiket Pending" - already in DashboardOverview as "Tiket Aktif"
                $completionRate = $totalTickets > 0
                    ? round(($completedTickets / $totalTickets) * 100, 1)
                    : 0;

                return [
                    Stat::make('Total Tiket', $totalTickets)
                        ->description('Semua tiket yang pernah dibuat')
                        ->descriptionIcon('heroicon-m-ticket')
                        ->chart([10, 25, 45, 68, 92, $totalTickets])
                        ->color('success'),

                    Stat::make('Tiket Bulan Ini', $ticketsThisMonth)
                        ->description('Tiket masuk bulan ' . now()->format('F'))
                        ->descriptionIcon('heroicon-m-calendar')
                        ->color('primary'),

                    Stat::make('Tiket Selesai', $completedTickets)
                        ->description("Tingkat penyelesaian: {$completionRate}%")
                        ->descriptionIcon('heroicon-m-check-badge')
                        ->color('info'),

                    Stat::make('Tiket Dibatalkan', $cancelledTickets)
                        ->description('Dibatalkan atau ditolak')
                        ->descriptionIcon('heroicon-m-x-circle')
                        ->color($cancelledTickets > 10 ? 'danger' : 'gray'),
                ];
            } catch (\Exception $e) {
                report($e);
                return [
                    Stat::make('Error', 'Database Error')
                        ->description('Could not load ticket stats')
                        ->color('danger'),
                ];
            }
        });
    }
}