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
    protected static string $view = 'filament.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            // QUICK OVERVIEW (8 Key Metrics in 4x2 grid)
            \App\Filament\Widgets\DashboardOverview::class,

            // DETAILED STATISTICS BY CATEGORY

            // 📊 User Management Section
            \App\Filament\Widgets\UserManagementHeading::class,
            UserRoleStats::class,
            RegistrationStats::class,

            // 🛠️ Services & Ticketing Section
            \App\Filament\Widgets\ServicesTicketingHeading::class,
            ServiceStats::class,
            TicketStats::class,

            // 👥 Visitor Management Section
            \App\Filament\Widgets\VisitorManagementHeading::class,
            VisitorStats::class,

            // 📢 Complaints & Reporting Section
            \App\Filament\Widgets\ComplaintsReportingHeading::class,
            ComplaintWhistleblowingStats::class,

            // 📋 Survey & Feedback Section
            \App\Filament\Widgets\SurveyFeedbackHeading::class,
            SurveyStats::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
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