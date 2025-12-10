<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\UserRoleStats;
use App\Filament\Widgets\ServiceStats;
use App\Filament\Widgets\TicketStats;
use App\Filament\Widgets\VisitorStats;
use App\Filament\Widgets\ComplaintWhistleblowingStats;
use App\Filament\Widgets\SurveyStats;
use App\Filament\Widgets\SkmSpakIndexChart;
use App\Filament\Widgets\RecentActivitiesWidget;
use App\Filament\Widgets\RegistrationStats;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            // ========================================
            // QUICK OVERVIEW (8 Key Metrics in 4x2 grid)
            // ========================================
            \App\Filament\Widgets\DashboardOverview::class,

            // ========================================
            // DETAILED STATISTICS BY CATEGORY
            // (No duplication - detailed breakdown only)
            // ========================================

            // 📊 User Management
            UserRoleStats::class,        // Breakdown by user roles
            RegistrationStats::class,    // Registration trends

            // 🛠️ Services & Ticketing
            ServiceStats::class,         // Service types & modes
            TicketStats::class,          // Ticket lifecycle stats

            // 👥 Visitor Management
            VisitorStats::class,         // Visitor trends & status

            // 📢 Complaints & Reporting
            ComplaintWhistleblowingStats::class,  // Complaints & WBS

            // 📋 Survey & Feedback
            SurveyStats::class,          // Survey responses
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Charts and detailed analytics in footer
            SkmSpakIndexChart::class,
            // Recent activities
            RecentActivitiesWidget::class,
        ];
    }

    public function getHeading(): string
    {
        return 'Dashboard Administrasi PTSP';
    }

    public function getSubheading(): ?string
    {
        return 'Ringkasan sistem pelayanan terpadu satu pintu MTsN 2 Kota Malang';
    }
}