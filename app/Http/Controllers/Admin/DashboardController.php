<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\Visitor;
use App\Models\Complaint;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Carbon\Carbon;
use App\Support\ServiceMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Admin dashboard (Modul 12): system inventory plus the same service
     * performance overview the leadership sees (ServiceMetrics).
     */
    public function index(Request $request)
    {
        $overview = ServiceMetrics::overview(ServiceMetrics::period($request->query('periode')));

        $system = [
            'users' => User::count(),
            'staff' => User::role(User::STAFF_ROLES)->count(),
            'services' => Service::where('is_active', true)->count(),
            'services_total' => Service::count(),
            'survey_active' => Survey::where('is_active', true)->count(),
        ];

        $recentTickets = Ticket::with(['user:id,name', 'service:id,name'])->latest()->limit(6)->get();
        $recentComplaints = Complaint::latest()->limit(6)->get(['id', 'complaint_number', 'complaint_type', 'title', 'status', 'created_at']);

        return view('admin.dashboard.index', $overview + compact('system', 'recentTickets', 'recentComplaints'));
    }

    /**
     * Get service performance chart data with filter
     */
    public function getServicePerformanceData(Request $request)
    {
        $filter = $request->input('filter', 'day'); // day, week, month, year

        $labels = [];
        $ticketsIncoming = [];
        $ticketsProcessing = [];
        $ticketsCompleted = [];

        switch ($filter) {
            case 'day':
                // Last 30 days
                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i)->format('Y-m-d');
                    $labels[] = Carbon::now()->subDays($i)->format('d M');

                    $ticketsIncoming[] = Ticket::whereDate('created_at', $date)
                        ->where('status', 'submitted')
                        ->count();

                    $ticketsProcessing[] = Ticket::whereDate('created_at', $date)
                        ->whereIn('status', ['processing', 'verified'])
                        ->count();

                    $ticketsCompleted[] = Ticket::whereDate('created_at', $date)
                        ->where('status', 'completed')
                        ->count();
                }
                break;

            case 'week':
                // Last 12 weeks
                for ($i = 11; $i >= 0; $i--) {
                    $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                    $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
                    $labels[] = 'W' . $startOfWeek->weekOfYear . ' ' . $startOfWeek->format('Y');

                    $ticketsIncoming[] = Ticket::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                        ->where('status', 'submitted')
                        ->count();

                    $ticketsProcessing[] = Ticket::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                        ->whereIn('status', ['processing', 'verified'])
                        ->count();

                    $ticketsCompleted[] = Ticket::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                        ->where('status', 'completed')
                        ->count();
                }
                break;

            case 'month':
                // Last 12 months
                for ($i = 11; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    $labels[] = $month->format('M Y');

                    $ticketsIncoming[] = Ticket::whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->where('status', 'submitted')
                        ->count();

                    $ticketsProcessing[] = Ticket::whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->whereIn('status', ['processing', 'verified'])
                        ->count();

                    $ticketsCompleted[] = Ticket::whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->where('status', 'completed')
                        ->count();
                }
                break;

            case 'year':
                // Last 5 years
                for ($i = 4; $i >= 0; $i--) {
                    $year = Carbon::now()->subYears($i)->year;
                    $labels[] = $year;

                    $ticketsIncoming[] = Ticket::whereYear('created_at', $year)
                        ->where('status', 'submitted')
                        ->count();

                    $ticketsProcessing[] = Ticket::whereYear('created_at', $year)
                        ->whereIn('status', ['processing', 'verified'])
                        ->count();

                    $ticketsCompleted[] = Ticket::whereYear('created_at', $year)
                        ->where('status', 'completed')
                        ->count();
                }
                break;
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                'incoming' => $ticketsIncoming,
                'processing' => $ticketsProcessing,
                'completed' => $ticketsCompleted
            ]
        ]);
    }

    /**
     * Get complaint performance chart data with filter
     */
    public function getComplaintPerformanceData(Request $request)
    {
        $filter = $request->input('filter', 'day');

        $labels = [];
        $complaintsIncoming = [];
        $complaintsProcessing = [];
        $complaintsCompleted = [];

        switch ($filter) {
            case 'day':
                // Last 30 days
                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i)->format('Y-m-d');
                    $labels[] = Carbon::now()->subDays($i)->format('d M');

                    $complaintsIncoming[] = Complaint::whereDate('created_at', $date)
                        ->whereIn('complaint_type', ['complaint', 'suggestion'])
                        ->where('status', 'submitted')
                        ->count();

                    $complaintsProcessing[] = Complaint::whereDate('created_at', $date)
                        ->whereIn('complaint_type', ['complaint', 'suggestion'])
                        ->whereIn('status', ['in_review', 'in_progress'])
                        ->count();

                    $complaintsCompleted[] = Complaint::whereDate('created_at', $date)
                        ->whereIn('complaint_type', ['complaint', 'suggestion'])
                        ->whereIn('status', ['resolved', 'closed'])
                        ->count();
                }
                break;

            case 'week':
                // Last 12 weeks
                for ($i = 11; $i >= 0; $i--) {
                    $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                    $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
                    $labels[] = 'W' . $startOfWeek->weekOfYear . ' ' . $startOfWeek->format('Y');

                    $complaintsIncoming[] = Complaint::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                        ->whereIn('complaint_type', ['complaint', 'suggestion'])
                        ->where('status', 'submitted')
                        ->count();

                    $complaintsProcessing[] = Complaint::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                        ->whereIn('complaint_type', ['complaint', 'suggestion'])
                        ->whereIn('status', ['in_review', 'in_progress'])
                        ->count();

                    $complaintsCompleted[] = Complaint::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                        ->whereIn('complaint_type', ['complaint', 'suggestion'])
                        ->whereIn('status', ['resolved', 'closed'])
                        ->count();
                }
                break;

            case 'month':
                // Last 12 months
                for ($i = 11; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    $labels[] = $month->format('M Y');

                    $complaintsIncoming[] = Complaint::whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->whereIn('complaint_type', ['complaint', 'suggestion'])
                        ->where('status', 'submitted')
                        ->count();

                    $complaintsProcessing[] = Complaint::whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->whereIn('complaint_type', ['complaint', 'suggestion'])
                        ->whereIn('status', ['in_review', 'in_progress'])
                        ->count();

                    $complaintsCompleted[] = Complaint::whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->whereIn('complaint_type', ['complaint', 'suggestion'])
                        ->whereIn('status', ['resolved', 'closed'])
                        ->count();
                }
                break;

            case 'year':
                // Last 5 years
                for ($i = 4; $i >= 0; $i--) {
                    $year = Carbon::now()->subYears($i)->year;
                    $labels[] = $year;

                    $complaintsIncoming[] = Complaint::whereYear('created_at', $year)
                        ->whereIn('complaint_type', ['complaint', 'suggestion'])
                        ->where('status', 'submitted')
                        ->count();

                    $complaintsProcessing[] = Complaint::whereYear('created_at', $year)
                        ->whereIn('complaint_type', ['complaint', 'suggestion'])
                        ->whereIn('status', ['in_review', 'in_progress'])
                        ->count();

                    $complaintsCompleted[] = Complaint::whereYear('created_at', $year)
                        ->whereIn('complaint_type', ['complaint', 'suggestion'])
                        ->whereIn('status', ['resolved', 'closed'])
                        ->count();
                }
                break;
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                'incoming' => $complaintsIncoming,
                'processing' => $complaintsProcessing,
                'completed' => $complaintsCompleted
            ]
        ]);
    }

    /**
     * Get whistleblowing performance chart data with filter
     */
    public function getWhistleblowingPerformanceData(Request $request)
    {
        $filter = $request->input('filter', 'day');

        $labels = [];
        $whistleblowingIncoming = [];
        $whistleblowingProcessing = [];
        $whistleblowingCompleted = [];

        switch ($filter) {
            case 'day':
                // Last 30 days
                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i)->format('Y-m-d');
                    $labels[] = Carbon::now()->subDays($i)->format('d M');

                    $whistleblowingIncoming[] = Complaint::whereDate('created_at', $date)
                        ->where('complaint_type', 'whistleblowing')
                        ->where('status', 'submitted')
                        ->count();

                    $whistleblowingProcessing[] = Complaint::whereDate('created_at', $date)
                        ->where('complaint_type', 'whistleblowing')
                        ->whereIn('status', ['in_review', 'in_progress'])
                        ->count();

                    $whistleblowingCompleted[] = Complaint::whereDate('created_at', $date)
                        ->where('complaint_type', 'whistleblowing')
                        ->whereIn('status', ['resolved', 'closed'])
                        ->count();
                }
                break;

            case 'week':
                // Last 12 weeks
                for ($i = 11; $i >= 0; $i--) {
                    $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                    $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
                    $labels[] = 'W' . $startOfWeek->weekOfYear . ' ' . $startOfWeek->format('Y');

                    $whistleblowingIncoming[] = Complaint::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                        ->where('complaint_type', 'whistleblowing')
                        ->where('status', 'submitted')
                        ->count();

                    $whistleblowingProcessing[] = Complaint::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                        ->where('complaint_type', 'whistleblowing')
                        ->whereIn('status', ['in_review', 'in_progress'])
                        ->count();

                    $whistleblowingCompleted[] = Complaint::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                        ->where('complaint_type', 'whistleblowing')
                        ->whereIn('status', ['resolved', 'closed'])
                        ->count();
                }
                break;

            case 'month':
                // Last 12 months
                for ($i = 11; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    $labels[] = $month->format('M Y');

                    $whistleblowingIncoming[] = Complaint::whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->where('complaint_type', 'whistleblowing')
                        ->where('status', 'submitted')
                        ->count();

                    $whistleblowingProcessing[] = Complaint::whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->where('complaint_type', 'whistleblowing')
                        ->whereIn('status', ['in_review', 'in_progress'])
                        ->count();

                    $whistleblowingCompleted[] = Complaint::whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->where('complaint_type', 'whistleblowing')
                        ->whereIn('status', ['resolved', 'closed'])
                        ->count();
                }
                break;

            case 'year':
                // Last 5 years
                for ($i = 4; $i >= 0; $i--) {
                    $year = Carbon::now()->subYears($i)->year;
                    $labels[] = $year;

                    $whistleblowingIncoming[] = Complaint::whereYear('created_at', $year)
                        ->where('complaint_type', 'whistleblowing')
                        ->where('status', 'submitted')
                        ->count();

                    $whistleblowingProcessing[] = Complaint::whereYear('created_at', $year)
                        ->where('complaint_type', 'whistleblowing')
                        ->whereIn('status', ['in_review', 'in_progress'])
                        ->count();

                    $whistleblowingCompleted[] = Complaint::whereYear('created_at', $year)
                        ->where('complaint_type', 'whistleblowing')
                        ->whereIn('status', ['resolved', 'closed'])
                        ->count();
                }
                break;
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                'incoming' => $whistleblowingIncoming,
                'processing' => $whistleblowingProcessing,
                'completed' => $whistleblowingCompleted
            ]
        ]);
    }

    /**
     * Get survey analytics data with filter
     */
    public function getSurveyAnalyticsData(Request $request)
    {
        $filter = $request->input('filter', 'day');

        // Determine date range based on filter
        $dateRange = $this->getDateRange($filter);

        // Get survey responses within date range
        $responses = SurveyResponse::whereBetween('created_at', $dateRange)
            ->with(['answers.question', 'ticket.service'])
            ->get();

        // Age categories - matching seeder options
        $ageCategories = [
            'Dibawah 20 Tahun' => 0,
            '21 s.d 30 Tahun' => 0,
            '31 s.d 40 Tahun' => 0,
            '41 s.d 50 Tahun' => 0,
            'Diatas 50 Tahun' => 0
        ];

        // Occupation categories
        $occupations = [];

        // Education categories
        $educationLevels = [];

        // Service types
        $serviceTypes = [];

        // Process responses
        foreach ($responses as $response) {
            foreach ($response->answers as $answer) {
                if (!$answer->question) continue;

                $questionText = $answer->question->question;

                // Age analysis
                if (str_contains($questionText, 'Usia')) {
                    $ageAnswer = $answer->selected_option ?? $answer->answer_text;
                    if ($ageAnswer && isset($ageCategories[$ageAnswer])) {
                        $ageCategories[$ageAnswer]++;
                    }
                }

                // Occupation analysis
                if (str_contains($questionText, 'Pekerjaan')) {
                    $occupation = $answer->selected_option ?? $answer->answer_text;
                    if ($occupation) {
                        $occupations[$occupation] = ($occupations[$occupation] ?? 0) + 1;
                    }
                }

                // Education analysis
                if (str_contains($questionText, 'Pendidikan')) {
                    $education = $answer->selected_option ?? $answer->answer_text;
                    if ($education) {
                        $educationLevels[$education] = ($educationLevels[$education] ?? 0) + 1;
                    }
                }

                // Service type analysis
                if (str_contains($questionText, 'Jenis Pelayanan')) {
                    $service = $answer->selected_option ?? $answer->answer_text;
                    if ($service) {
                        $serviceTypes[$service] = ($serviceTypes[$service] ?? 0) + 1;
                    }
                }
            }
        }

        // Calculate satisfaction index (1-5 scale converted to 0-100)
        $totalResponses = $responses->count();
        $avgRating = 0;

        if ($totalResponses > 0) {
            try {
                $avgRating = DB::table('survey_responses')
                    ->join('survey_answers', 'survey_responses.id', '=', 'survey_answers.survey_response_id')
                    ->whereBetween('survey_responses.created_at', $dateRange)
                    ->whereNotNull('survey_answers.rating_value')
                    ->avg('survey_answers.rating_value') ?? 0;
            } catch (\Exception $e) {
                $avgRating = 0;
            }
        }

        // Convert to satisfaction index (0-100)
        $satisfactionIndex = $totalResponses > 0 ? ($avgRating / 4) * 100 : 0; // SKM uses 1-4 scale

        // Response trend over time
        $responseTrend = $this->getResponseTrend($filter, $dateRange);

        return response()->json([
            'respondents' => $totalResponses,
            'satisfactionIndex' => round($satisfactionIndex, 2),
            'avgRating' => round($avgRating, 2),
            'ageDistribution' => [
                'labels' => array_keys($ageCategories),
                'data' => array_values($ageCategories)
            ],
            'occupationDistribution' => [
                'labels' => array_keys($occupations),
                'data' => array_values($occupations)
            ],
            'educationDistribution' => [
                'labels' => array_keys($educationLevels),
                'data' => array_values($educationLevels)
            ],
            'serviceDistribution' => [
                'labels' => array_keys($serviceTypes),
                'data' => array_values($serviceTypes)
            ],
            'responseTrend' => $responseTrend
        ]);
    }

    /**
     * Get date range based on filter
     */
    private function getDateRange($filter)
    {
        switch ($filter) {
            case 'day':
                return [Carbon::now()->subDays(30), Carbon::now()];
            case 'week':
                return [Carbon::now()->subWeeks(12), Carbon::now()];
            case 'month':
                return [Carbon::now()->subMonths(12), Carbon::now()];
            case 'year':
                return [Carbon::now()->subYears(5), Carbon::now()];
            default:
                return [Carbon::now()->subDays(30), Carbon::now()];
        }
    }

    /**
     * Get response trend over time
     */
    private function getResponseTrend($filter, $dateRange)
    {
        $labels = [];
        $data = [];

        switch ($filter) {
            case 'day':
                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $labels[] = $date->format('d M');
                    $data[] = SurveyResponse::whereDate('created_at', $date)->count();
                }
                break;

            case 'week':
                for ($i = 11; $i >= 0; $i--) {
                    $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                    $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
                    $labels[] = 'W' . $startOfWeek->weekOfYear;
                    $data[] = SurveyResponse::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
                }
                break;

            case 'month':
                for ($i = 11; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    $labels[] = $month->format('M Y');
                    $data[] = SurveyResponse::whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->count();
                }
                break;

            case 'year':
                for ($i = 4; $i >= 0; $i--) {
                    $year = Carbon::now()->subYears($i)->year;
                    $labels[] = $year;
                    $data[] = SurveyResponse::whereYear('created_at', $year)->count();
                }
                break;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}