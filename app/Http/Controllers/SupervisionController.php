<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyAnswer;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupervisionController extends Controller
{
    /**
     * Complaint dashboard
     */
    public function complaints()
    {
        $stats = [
            'total_complaints' => Complaint::count(),
            'pending_complaints' => Complaint::where('status', 'pending')->count(),
            'processing_complaints' => Complaint::where('status', 'processing')->count(),
            'completed_complaints' => Complaint::where('status', 'completed')->count(),
        ];

        $recentComplaints = Complaint::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('supervision.complaints', compact('stats', 'recentComplaints'));
    }

    /**
     * Submit complaint form
     */
    public function submitComplaintForm()
    {
        return view('supervision.complaint-form');
    }

    /**
     * Submit complaint
     */
    public function submitComplaint(Request $request)
    {
        $validated = $request->validate([
            'complaint_type' => 'required|in:complaint,suggestion,whistleblowing',
            'reporter_name' => 'required|string|max:255',
            'reporter_email' => 'nullable|email|max:255',
            'reporter_phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'related_ticket_number' => 'nullable|string|max:50',
            'evidence_files.*' => 'nullable|file|max:10240',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $complaint = Complaint::create([
            'complaint_number' => $this->generateComplaintNumber(),
            'complaint_type' => $validated['complaint_type'],
            'reporter_name' => $validated['is_anonymous'] ? 'Anonim' : $validated['reporter_name'],
            'reporter_email' => $validated['is_anonymous'] ? null : $validated['reporter_email'],
            'reporter_phone' => $validated['is_anonymous'] ? null : $validated['reporter_phone'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'related_ticket_number' => $validated['related_ticket_number'] ?? null,
            'status' => 'pending',
            'is_anonymous' => $validated['is_anonymous'] ?? false,
        ]);

        // Handle evidence files
        if ($request->hasFile('evidence_files')) {
            foreach ($request->file('evidence_files') as $file) {
                $path = $file->store('complaint-evidence', 'public');

                $complaint->evidenceFiles()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('supervision.complaint.success', $complaint->complaint_number)
            ->with('success', 'Pengaduan berhasil disimpan dengan nomor: ' . $complaint->complaint_number);
    }

    /**
     * Complaint success
     */
    public function complaintSuccess($complaintNumber)
    {
        $complaint = Complaint::where('complaint_number', $complaintNumber)->firstOrFail();
        return view('supervision.complaint-success', compact('complaint'));
    }

    /**
     * Track complaint
     */
    public function trackComplaint(Request $request)
    {
        $validated = $request->validate([
            'complaint_number' => 'required|string',
            'reporter_email' => 'nullable|email',
        ]);

        $complaint = Complaint::where('complaint_number', $validated['complaint_number']);

        // If email provided, verify ownership (unless anonymous)
        if (!empty($validated['reporter_email']) && !$complaint->is_anonymous) {
            $complaint->where('reporter_email', $validated['reporter_email']);
        }

        $complaint = $complaint->firstOrFail();

        return view('supervision.complaint-track-result', compact('complaint'));
    }

    /**
     * Whistleblowing form
     */
    public function whistleblowingForm()
    {
        return view('supervision.whistleblowing-form');
    }

    /**
     * Submit whistleblowing
     */
    public function submitWhistleblowing(Request $request)
    {
        $validated = $request->validate([
            'reporter_name' => 'required|string|max:255', // Will be marked as anonymous
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'involved_parties' => 'nullable|string|max:500',
            'incident_date' => 'nullable|date',
            'incident_location' => 'nullable|string|max:255',
            'evidence_files.*' => 'nullable|file|max:10240',
        ]);

        $complaint = Complaint::create([
            'complaint_number' => $this->generateComplaintNumber(),
            'complaint_type' => 'whistleblowing',
            'reporter_name' => 'Anonim', // Always anonymous for whistleblowing
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'involved_parties' => $validated['involved_parties'] ?? null,
            'incident_date' => $validated['incident_date'] ?? null,
            'incident_location' => $validated['incident_location'] ?? null,
            'status' => 'pending',
            'is_anonymous' => true,
            'is_confidential' => true,
        ]);

        // Handle evidence files
        if ($request->hasFile('evidence_files')) {
            foreach ($request->file('evidence_files') as $file) {
                $path = $file->store('whistleblowing-evidence', 'public');

                $complaint->evidenceFiles()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('supervision.whistleblowing.success', $complaint->complaint_number)
            ->with('success', 'Laporan whistleblowing berhasil disimpan dengan nomor: ' . $complaint->complaint_number);
    }

    /**
     * Whistleblowing success
     */
    public function whistleblowingSuccess($complaintNumber)
    {
        $complaint = Complaint::where('complaint_number', $complaintNumber)->firstOrFail();
        return view('supervision.whistleblowing-success', compact('complaint'));
    }

    /**
     * SKM Survey form
     */
    public function skmSurveyForm()
    {
        $ticketNumber = request('ticket_number');

        // Verify ticket exists and is completed
        if ($ticketNumber) {
            $ticket = Ticket::where('ticket_number', $ticketNumber)
                ->where('status', 'completed')
                ->first();

            if (!$ticket) {
                return redirect()->route('supervision.skm.form')
                    ->with('error', 'Nomor tiket tidak valid atau tiket belum selesai.');
            }
        }

        $survey = Survey::where('type', 'skm')
            ->where('is_active', true)
            ->with('questions')
            ->firstOrFail();

        return view('supervision.skm-survey-form', compact('survey', 'ticketNumber', 'ticket'));
    }

    /**
     * Submit SKM Survey
     */
    public function submitSkmSurvey(Request $request)
    {
        $validated = $request->validate([
            'survey_id' => 'required|exists:surveys,id',
            'ticket_number' => 'nullable|string|max:50',
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|exists:survey_questions,id',
            'responses.*.answer' => 'required|integer|between:1,4',
            'suggestions' => 'nullable|string|max:1000',
        ]);

        $survey = Survey::findOrFail($validated['survey_id']);
        $ticket = null;

        if (!empty($validated['ticket_number'])) {
            $ticket = Ticket::where('ticket_number', $validated['ticket_number'])
                ->where('status', 'completed')
                ->first();
        }

        // Create survey response
        $surveyResponse = SurveyResponse::create([
            'survey_id' => $survey->id,
            'ticket_id' => $ticket?->id,
            'respondent_ip' => $request->ip(),
            'respondent_agent' => $request->userAgent(),
            'suggestions' => $validated['suggestions'] ?? null,
        ]);

        // Save answers
        foreach ($validated['responses'] as $response) {
            SurveyAnswer::create([
                'survey_response_id' => $surveyResponse->id,
                'survey_question_id' => $response['question_id'],
                'answer' => $response['answer'],
            ]);
        }

        return redirect()->route('supervision.skm.success')
            ->with('success', 'Terima kasih atas partisipasi Anda dalam survei kepuasan masyarakat.');
    }

    /**
     * Survey success page
     */
    public function surveySuccess()
    {
        return view('supervision.survey-success');
    }

    /**
     * Survey management (admin only)
     */
    public function surveyManagement()
    {
        $surveys = Survey::with('questions')
            ->orderBy('type')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total_surveys' => Survey::count(),
            'total_responses' => SurveyResponse::count(),
            'skm_responses' => SurveyResponse::whereHas('survey', function($query) {
                $query->where('type', 'skm');
            })->count(),
            'spak_responses' => SurveyResponse::whereHas('survey', function($query) {
                $query->where('type', 'spak');
            })->count(),
        ];

        return view('supervision.survey-management', compact('surveys', 'stats'));
    }

    /**
     * Survey results
     */
    public function surveyResults($surveyId)
    {
        $survey = Survey::with(['questions', 'responses.answers'])
            ->findOrFail($surveyId);

        // Calculate results
        $results = [];
        foreach ($survey->questions as $question) {
            $answers = $question->answers()->pluck('answer');
            $totalAnswers = $answers->count();

            if ($totalAnswers > 0) {
                $results[$question->id] = [
                    'question' => $question,
                    'total_responses' => $totalAnswers,
                    'average_score' => round($answers->avg(), 2),
                    'distribution' => [
                        1 => $answers->where('answer', 1)->count(),
                        2 => $answers->where('answer', 2)->count(),
                        3 => $answers->where('answer', 3)->count(),
                        4 => $answers->where('answer', 4)->count(),
                    ],
                ];
            }
        }

        // Calculate overall satisfaction index
        $overallScore = 0;
        $totalQuestions = count($results);
        if ($totalQuestions > 0) {
            $totalScore = array_sum(array_column($results, 'average_score'));
            $overallScore = round($totalScore / $totalQuestions, 2);
        }

        // Convert to satisfaction index (1-4 scale to 0-100 scale)
        $satisfactionIndex = round(($overallScore - 1) / 3 * 100, 2);

        return view('supervision.survey-results', compact(
            'survey',
            'results',
            'overallScore',
            'satisfactionIndex'
        ));
    }

    /**
     * Performance dashboard
     */
    public function performance()
    {
        $period = request('period', 'month');
        $startDate = match($period) {
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'quarter' => Carbon::now()->startOfQuarter(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        $endDate = Carbon::now();

        // Ticket performance
        $ticketStats = [
            'total' => Ticket::whereBetween('created_at', [$startDate, $endDate])->count(),
            'completed' => Ticket::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')->count(),
            'average_time' => $this->calculateAverageCompletionTime($startDate, $endDate),
        ];

        // Complaint performance
        $complaintStats = [
            'total' => Complaint::whereBetween('created_at', [$startDate, $endDate])->count(),
            'completed' => Complaint::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')->count(),
            'by_type' => Complaint::select('complaint_type', DB::raw('count(*) as count'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('complaint_type')
                ->get(),
        ];

        // Survey performance
        $surveyStats = [
            'total_responses' => SurveyResponse::whereBetween('created_at', [$startDate, $endDate])->count(),
            'satisfaction_index' => $this->calculateSatisfactionIndex($startDate, $endDate),
        ];

        return view('supervision.performance', compact(
            'period',
            'ticketStats',
            'complaintStats',
            'surveyStats'
        ));
    }

    /**
     * Generate complaint number
     */
    private function generateComplaintNumber()
    {
        $prefix = match(request()->segment(2)) {
            'complaints' => 'KPL',
            'whistleblowing' => 'WBL',
            default => 'KPL',
        };

        $date = Carbon::now()->format('Ym');
        $sequence = Complaint::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    /**
     * Calculate average completion time
     */
    private function calculateAverageCompletionTime($startDate, $endDate)
    {
        $completedTickets = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->get();

        if ($completedTickets->isEmpty()) {
            return 0;
        }

        $totalMinutes = $completedTickets->sum(function($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->completed_at);
        });

        return round($totalMinutes / $completedTickets->count(), 2);
    }

    /**
     * Calculate satisfaction index
     */
    private function calculateSatisfactionIndex($startDate, $endDate)
    {
        $surveyResponses = SurveyResponse::whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('survey', function($query) {
                $query->where('type', 'skm');
            })
            ->with('answers.question')
            ->get();

        if ($surveyResponses->isEmpty()) {
            return 0;
        }

        $totalScore = 0;
        $totalAnswers = 0;

        foreach ($surveyResponses as $response) {
            foreach ($response->answers as $answer) {
                $totalScore += $answer->answer;
                $totalAnswers++;
            }
        }

        if ($totalAnswers === 0) {
            return 0;
        }

        $averageScore = $totalScore / $totalAnswers;
        return round(($averageScore - 1) / 3 * 100, 2);
    }
}