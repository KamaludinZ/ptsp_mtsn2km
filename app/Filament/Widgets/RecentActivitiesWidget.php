<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Ticket;
use App\Models\Complaint;
use App\Models\Whistleblowing;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RecentActivitiesWidget extends Widget
{
    protected static bool $isDiscovered = false;

    /** Part of the administrator's dashboard. */
    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    protected static ?int $sort = 9;

    protected static string $view = 'filament.widgets.recent-activities';

    protected static ?string $pollingInterval = null;

    protected function getViewData(): array
    {
        // Load recent activities data
        $recentData = Cache::remember('admin_dashboard_recent_activities', 120, function () {
            try {
                return [
                    'recentTickets' => Ticket::with('user', 'service')
                        ->latest()
                        ->limit(5)
                        ->get(),
                    'recentComplaints' => Complaint::with('assignedTo')
                        ->latest()
                        ->limit(5)
                        ->get(),
                    'recentWhistleblowing' => Whistleblowing::latest()
                        ->limit(5)
                        ->get(),
                    // Latest respondent per user (DISTINCT ON + ORDER BY created_at
                    // is invalid on PostgreSQL, so de-duplicate in PHP)
                    'recentSurveyCompletions' => SurveyResponse::with('user')
                        ->whereNotNull('user_id')
                        ->latest()
                        ->limit(25)
                        ->get()
                        ->unique('user_id')
                        ->take(5)
                        ->values(),
                    'recentVerifiedRegistrations' => User::where('email_verified_at', '!=', null)
                        ->latest('email_verified_at')
                        ->limit(5)
                        ->get()
                ];
            } catch (\Throwable $e) {
                report($e);
                return [
                    'recentTickets' => collect(),
                    'recentComplaints' => collect(),
                    'recentWhistleblowing' => collect(),
                    'recentSurveyCompletions' => collect(),
                    'recentVerifiedRegistrations' => collect()
                ];
            }
        });

        return $recentData;
    }
}