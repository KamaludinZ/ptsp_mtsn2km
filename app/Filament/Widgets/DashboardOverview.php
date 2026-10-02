<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\Visitor;
use App\Models\Complaint;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\Cache;

class DashboardOverview extends BaseWidget
{
    protected static bool $isDiscovered = false;

    /** Part of the administrator's dashboard. */
    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4; // 4 columns untuk layout yang lebih rapi
    }

    protected function getStats(): array
    {
        return Cache::remember('dashboard_overview_stats', 60, function () {
            try {
                // Core metrics
                $totalUsers = User::count();
                $activeServices = Service::where('is_active', true)->count();
                $activeTickets = Ticket::whereIn('status', ['submitted', 'verified', 'in_process', 'pending_approval'])->count();
                $todayVisitors = Visitor::whereDate('created_at', now()->today())->count();

                // Complaint & WBS
                $pendingComplaints = Complaint::where('complaint_type', 'complaint')
                    ->whereIn('status', ['submitted', 'in_review', 'in_progress'])
                    ->count();
                $pendingWBS = Complaint::where('complaint_type', 'whistleblowing')
                    ->whereIn('status', ['submitted', 'in_review', 'in_progress'])
                    ->count();

                // Survey responses this month
                $surveyResponsesMonth = SurveyResponse::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count();

                // Recent activity (last 7 days)
                $recentActivity = Ticket::where('created_at', '>', now()->subDays(7))->count() +
                                 Complaint::where('created_at', '>', now()->subDays(7))->count();

                return [
                    // Row 1: Core System Metrics
                    Stat::make('Total Pengguna', $totalUsers)
                        ->description('Pengguna terdaftar di sistem')
                        ->descriptionIcon('heroicon-m-users')
                        ->chart([7, 12, 18, 22, 28, 35, $totalUsers])
                        ->color('success'),

                    Stat::make('Layanan Aktif', $activeServices)
                        ->description('Layanan tersedia saat ini')
                        ->descriptionIcon('heroicon-m-cog-6-tooth')
                        ->chart([3, 5, 8, 12, $activeServices])
                        ->color('primary'),

                    Stat::make('Tiket Aktif', $activeTickets)
                        ->description('Tiket sedang diproses')
                        ->descriptionIcon('heroicon-m-ticket')
                        ->color($activeTickets > 20 ? 'warning' : 'info'),

                    Stat::make('Pengunjung Hari Ini', $todayVisitors)
                        ->description('Tamu hari ini')
                        ->descriptionIcon('heroicon-m-user-group')
                        ->color('warning'),

                    // Row 2: Pending Items & Activity
                    Stat::make('Pengaduan Pending', $pendingComplaints)
                        ->description('Menunggu tindak lanjut')
                        ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                        ->color($pendingComplaints > 0 ? 'danger' : 'gray'),

                    Stat::make('WBS Pending', $pendingWBS)
                        ->description('Whistleblowing System')
                        ->descriptionIcon('heroicon-m-shield-check')
                        ->color($pendingWBS > 0 ? 'danger' : 'gray'),

                    Stat::make('Survei Bulan Ini', $surveyResponsesMonth)
                        ->description('Responden survei')
                        ->descriptionIcon('heroicon-m-clipboard-document-check')
                        ->color('purple'),

                    Stat::make('Aktivitas 7 Hari', $recentActivity)
                        ->description('Total aktivitas minggu ini')
                        ->descriptionIcon('heroicon-m-calendar-days')
                        ->chart([12, 18, 25, 32, 28, 35, $recentActivity])
                        ->color('indigo'),
                ];
            } catch (\Exception $e) {
                report($e);
                return [
                    Stat::make('Error', 'Database Error')
                        ->description('Tidak dapat memuat statistik')
                        ->color('danger'),
                ];
            }
        });
    }
}
