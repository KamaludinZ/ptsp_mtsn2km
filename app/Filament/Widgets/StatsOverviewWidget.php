<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Service;
use App\Models\Complaint;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Optimized queries
        $ticketCountsByStatus = Ticket::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $ticketsToday = Ticket::whereDate('created_at', Carbon::today())->count();
        $ticketsYesterday = Ticket::whereDate('created_at', Carbon::yesterday())->count();

        $usersToday = User::whereDate('created_at', Carbon::today())->count();
        $usersYesterday = User::whereDate('created_at', Carbon::yesterday())->count();

        $visitorsToday = Visitor::whereDate('created_at', Carbon::today())->count();
        $visitorsYesterday = Visitor::whereDate('created_at', Carbon::yesterday())->count();

        $pendingComplaints = Complaint::where('status', 'submitted')->count();

        // Prepare chart data (simple last 7 days)
        $ticketsLast7Days = Ticket::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('count', 'date')
            ->toArray();
        
        $usersLast7Days = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('count', 'date')
            ->toArray();

        $chartData = [];
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartData['tickets'][] = $ticketsLast7Days[$date] ?? 0;
            $chartData['users'][] = $usersLast7Days[$date] ?? 0;
        }
        $chartData['tickets'] = array_reverse($chartData['tickets']);
        $chartData['users'] = array_reverse($chartData['users']);

        return [
            Stat::make('Total Tiket', $ticketCountsByStatus->sum())
                ->description('Tiket yang masuk hari ini: ' . $ticketsToday)
                ->descriptionIcon($ticketsToday >= $ticketsYesterday ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($chartData['tickets'])
                ->color($ticketsToday >= $ticketsYesterday ? 'success' : 'danger'),

            Stat::make('Tiket Pending', $ticketCountsByStatus->get('submitted', 0))
                ->description('Menunggu diproses')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Pengguna', User::count())
                ->description('Pengguna baru hari ini: ' . $usersToday)
                ->descriptionIcon($usersToday >= $usersYesterday ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($chartData['users'])
                ->color($usersToday >= $usersYesterday ? 'success' : 'danger'),

            Stat::make('Pengunjung Hari Ini', $visitorsToday)
                ->description($visitorsToday >= $visitorsYesterday ? 'Tren naik dari kemarin' : 'Tren turun dari kemarin')
                ->descriptionIcon($visitorsToday >= $visitorsYesterday ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('info'),
        ];
    }
}