<?php

namespace App\Filament\Pages;

use App\Filament\Widgets;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * The staff landing page. Every role gets its own overview; the widgets
 * decide for themselves whom they are shown to (canView).
 */
class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            // Pimpinan (Kepala Sekolah, Kepala TU)
            Widgets\Leadership\LeadershipStats::class,
            Widgets\Leadership\PendingApprovals::class,

            // Loket
            Widgets\FrontDesk\FrontDeskStats::class,
            Widgets\FrontDesk\PickupTickets::class,
            Widgets\FrontDesk\TodayVisitors::class,

            // Back Office
            Widgets\BackOffice\BackOfficeStats::class,
            Widgets\BackOffice\TicketQueue::class,

            // Pengawasan
            Widgets\Supervision\SupervisionStats::class,
            Widgets\Supervision\NewComplaints::class,

            // Administrator
            Widgets\DashboardOverview::class,
            Widgets\UserManagementHeading::class,
            Widgets\UserRoleStats::class,
            Widgets\RegistrationStats::class,
            Widgets\ServicesTicketingHeading::class,
            Widgets\ServiceStats::class,
            Widgets\TicketStats::class,
            Widgets\VisitorManagementHeading::class,
            Widgets\VisitorStats::class,
            Widgets\ComplaintsReportingHeading::class,
            Widgets\ComplaintWhistleblowingStats::class,
            Widgets\SurveyFeedbackHeading::class,
            Widgets\SurveyStats::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            Widgets\RecentActivitiesWidget::class,
        ];
    }

    public function getHeading(): string
    {
        $user = auth()->user();

        return match (true) {
            $user->hasRole('admin') => 'Dashboard Administrasi PTSP',
            $user->hasAnyRole(['kepala_sekolah', 'kepala_tu']) => 'Dashboard Pimpinan',
            $user->hasRole('back_office') => 'Dashboard Back Office',
            $user->hasRole('front_desk') => 'Dashboard Loket',
            $user->hasRole('supervisor') => 'Dashboard Pengawasan',
            default => 'Dashboard',
        };
    }

    public function getSubheading(): ?string
    {
        return 'Selamat datang, ' . auth()->user()->name . '. ' . now()->translatedFormat('l, j F Y') . '.';
    }
}
