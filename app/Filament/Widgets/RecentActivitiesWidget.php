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
    protected static ?int $sort = 9;

    protected string $view;

    protected ?string $pollingInterval = null;

    public function __construct()
    {
        $this->view = 'filament.widgets.recent-activities';
    }

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
                    'recentSurveyCompletions' => SurveyResponse::with('user')
                        ->select('user_id', 'created_at')
                        ->distinct('user_id')
                        ->latest()
                        ->limit(5)
                        ->get(),
                    'recentVerifiedRegistrations' => User::where('email_verified_at', '!=', null)
                        ->latest('email_verified_at')
                        ->limit(5)
                        ->get()
                ];
            } catch (\Exception $e) {
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