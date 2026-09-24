<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyAnswer;
use App\Models\Ticket;
use App\Mail\SurveyThankYouMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
            'reporter_email' => 'required|email|max:255',
            'reporter_phone' => 'nullable|string|max:20',
            'complaint_title' => 'required|string|max:255',
            'complaint_description' => 'required|string|max:2000',
            'incident_date' => 'nullable|date|before_or_equal:today',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf',
        ]);

        $complaint = Complaint::create([
            'complaint_number' => $this->generateComplaintNumber(),
            'complaint_type' => $validated['complaint_type'],
            'reporter_name' => $validated['reporter_name'],
            'reporter_email' => $validated['reporter_email'],
            'reporter_phone' => $validated['reporter_phone'] ?? null,
            'complainant_name' => $validated['reporter_name'],
            'complainant_email' => $validated['reporter_email'],
            'complainant_contact' => $validated['reporter_phone'] ?? null,
            'title' => $validated['complaint_title'],
            'subject' => $validated['complaint_title'],
            'description' => $validated['complaint_description'],
            'incident_date' => $validated['incident_date'] ?? null,
            'status' => 'submitted',
            'anonymous' => false,
        ]);

        $this->storeEvidenceFile($request, $complaint);

        return redirect()->route('supervision.complaint.success', $complaint->complaint_number)
            ->with('success', 'Pengaduan berhasil disimpan dengan nomor: ' . $complaint->complaint_number);
    }

    /**
     * Store the (optional) single evidence attachment as a JSON-encoded
     * array of storage paths on the complaint's evidence_files column.
     */
    private function storeEvidenceFile(Request $request, Complaint $complaint): void
    {
        if (!$request->hasFile('attachment')) {
            return;
        }

        $files = $request->file('attachment');
        $files = is_array($files) ? $files : [$files];

        $paths = [];
        foreach ($files as $file) {
            // Stored privately — evidence (especially whistleblowing) must not
            // be reachable via a guessable public URL. Served only through
            // Admin\ComplaintController::downloadEvidence() to admin staff.
            $paths[] = $file->store('complaint-evidence', 'local');
        }

        $complaint->update(['evidence_files' => json_encode($paths)]);
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
     * Show complaint tracking form
     */
    public function showTrackForm()
    {
        return view('supervision.complaint-track-form');
    }

    /**
     * Track complaint
     */
    public function trackComplaint(Request $request)
    {
        $validated = $request->validate([
            'complaint_number' => 'required|string',
            'reporter_email' => 'required|email',
        ]);

        // Ownership must always be verified by the reporter's own email —
        // never look up a complaint (especially whistleblowing) by number alone.
        $complaint = Complaint::where('complaint_number', $validated['complaint_number'])
            ->where(function ($q) use ($validated) {
                $q->where('reporter_email', $validated['reporter_email'])
                  ->orWhere('complainant_email', $validated['reporter_email']);
            })
            ->firstOrFail();

        return view('supervision.complaint-track-result', compact('complaint'));
    }

    /**
     * Whistleblowing form
     */
    public function whistleblowingForm()
    {
        return view('supervision.complaint-form', ['activeTab' => 'whistleblowing']);
    }

    /**
     * Submit whistleblowing
     */
    public function submitWhistleblowing(Request $request)
    {
        $validated = $request->validate([
            'violation_category' => 'nullable|string|max:255',
            'complaint_title' => 'required|string|max:255',
            'complaint_description' => 'required|string|max:2000',
            'incident_date' => 'nullable|date',
            'anonymous' => 'nullable|boolean',
            'reporter_name' => 'nullable|string|max:255',
            'reporter_email' => 'nullable|email|max:255',
            'reporter_phone' => 'nullable|string|max:20',
            'attachment.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf',
        ]);

        $isAnonymous = (bool) ($validated['anonymous'] ?? false);

        $complaint = Complaint::create([
            'complaint_number' => $this->generateComplaintNumber(),
            'complaint_type' => 'whistleblowing',
            'category' => $validated['violation_category'] ?? null,
            'title' => $validated['complaint_title'],
            'subject' => $validated['complaint_title'],
            'description' => $validated['complaint_description'],
            'incident_date' => $validated['incident_date'] ?? null,
            'reporter_name' => $isAnonymous ? 'Anonim' : ($validated['reporter_name'] ?? 'Anonim'),
            'reporter_email' => $isAnonymous ? null : ($validated['reporter_email'] ?? null),
            'reporter_phone' => $isAnonymous ? null : ($validated['reporter_phone'] ?? null),
            'status' => 'submitted',
            'anonymous' => $isAnonymous,
            'is_whistleblowing' => true,
            'is_confidential' => true,
        ]);

        $this->storeEvidenceFile($request, $complaint);

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
        // The public survey is the 3-step SKM/SPAK flow (SurveyController),
        // whose questions are managed from the admin survey menu. Keep this
        // URL working for old links and e-mails.
        return redirect()->route('survey.form');
    }

    /**
     * Validate ticket code (AJAX)
     */
    public function validateTicketCode(Request $request)
    {
        $ticketCode = $request->input('ticket_code');

        // Check if ticket exists
        $ticket = Ticket::where('ticket_number', $ticketCode)->first();

        if (!$ticket) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode tiket tidak ditemukan. Pastikan Anda memasukkan kode tiket yang benar.'
            ]);
        }

        // Check if ticket is completed
        if ($ticket->status !== Ticket::STATUS_COMPLETED) {
            return response()->json([
                'valid' => false,
                'message' => 'Tiket belum selesai. Survei hanya dapat diisi untuk tiket yang sudah selesai.'
            ]);
        }

        // Check if survey has already been filled for this ticket
        $existingResponse = SurveyResponse::where('ticket_code', $ticketCode)->first();

        if ($existingResponse) {
            return response()->json([
                'valid' => false,
                'message' => 'Survei untuk tiket ini sudah pernah diisi. Setiap tiket hanya dapat mengisi survei satu kali.'
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Kode tiket valid. Anda dapat melanjutkan mengisi survei.',
            'data' => [
                'email' => $ticket->email,
                'service_type' => $ticket->service_id ? $ticket->service->service_slug : null,
                'service_name' => $ticket->service_id ? $ticket->service->name : null,
            ]
        ]);
    }

    /**
     * Submit SKM Survey
     */
    public function submitSkmSurvey(Request $request)
    {
        // Validate basic respondent data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'education' => 'required|string|max:255',
            'occupation' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'ticket_code' => 'required|string|exists:tickets,ticket_number',
            'email' => 'required|email|max:255',
            'service_type' => 'required|string|max:255',
            // SKM answers validation
            'skm_answers' => 'required|array',
            'skm_answers.*' => 'required|integer|between:1,4',
            // SPAK answers validation
            'spak_answers' => 'required|array',
            'spak_answers.*' => 'required|string',
            'spak_suggestions' => 'nullable|string|max:1000',
        ]);

        // Validate ticket code hasn't been used for survey
        $existingResponse = SurveyResponse::where('ticket_code', $validated['ticket_code'])->first();
        if ($existingResponse) {
            return redirect()->back()
                ->withErrors(['ticket_code' => 'Kode tiket ini sudah pernah digunakan untuk mengisi survei.'])
                ->withInput();
        }

        // Get the active survey edition
        $activeEdition = \App\Models\SurveyEdition::where('is_active', true)->first();

        // Create survey response for SKM
        $surveyResponse = SurveyResponse::create([
            'survey_id' => null, // This is not used anymore
            'survey_edition_id' => $activeEdition ? $activeEdition->id : null,
            'ticket_code' => $validated['ticket_code'],
            'respondent_email' => $validated['email'],
            'respondent_name' => $validated['name'],
            'respondent_age' => $validated['age'],
            'respondent_gender' => $validated['gender'],
            'respondent_education' => $validated['education'],
            'respondent_occupation' => $validated['occupation'],
            'respondent_phone' => $validated['phone'] ?? null,
            'ip_address' => $request->ip(),
            'completed_at' => now(),
        ]);

        // Save SKM answers
        foreach ($validated['skm_answers'] as $questionIndex => $answerValue) {
            SurveyAnswer::create([
                'survey_response_id' => $surveyResponse->id,
                'survey_question_id' => null,
                'rating_value' => $answerValue,
                'answer_text' => "SKM Question {$questionIndex}: {$answerValue}",
            ]);
        }

        // Save SPAK answers
        foreach ($validated['spak_answers'] as $questionIndex => $answerValue) {
            SurveyAnswer::create([
                'survey_response_id' => $surveyResponse->id,
                'survey_question_id' => null,
                'rating_value' => null,
                'answer_text' => "SPAK Question {$questionIndex}: {$answerValue}",
            ]);
        }

        // Save SPAK suggestions if provided
        if (!empty($validated['spak_suggestions'])) {
            SurveyAnswer::create([
                'survey_response_id' => $surveyResponse->id,
                'survey_question_id' => null,
                'rating_value' => null,
                'answer_text' => 'SPAK Suggestions: ' . $validated['spak_suggestions'],
            ]);
        }

        // Send thank you email
        try {
            Mail::to($validated['email'])->send(new SurveyThankYouMail($surveyResponse, $validated['name']));
        } catch (\Exception $e) {
            // Log error but don't fail the submission
            \Log::error('Failed to send survey thank you email: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Terima kasih atas partisipasi Anda dalam survei ini. Email ucapan terima kasih telah dikirim ke ' . $validated['email']);
    }

    /**
     * Survey success page
     */
    public function surveySuccess()
    {
        // Deprecated route kept only for backward-compatible links;
        // the current survey flow's success page lives under survey.success.
        return redirect()->route('survey.success');
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
            $answers = $question->answers()->whereNotNull('rating_value')->pluck('rating_value');
            $totalAnswers = $answers->count();

            if ($totalAnswers > 0) {
                $results[$question->id] = [
                    'question' => $question,
                    'total_responses' => $totalAnswers,
                    'average_score' => round($answers->avg(), 2),
                    'distribution' => [
                        1 => $answers->where(fn ($v) => (int) $v === 1)->count(),
                        2 => $answers->where(fn ($v) => (int) $v === 2)->count(),
                        3 => $answers->where(fn ($v) => (int) $v === 3)->count(),
                        4 => $answers->where(fn ($v) => (int) $v === 4)->count(),
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

        // Count alone can collide with a soft-deleted row still holding a
        // number (unique constraint applies regardless of deleted_at), so
        // derive the next sequence from the highest existing number instead.
        $lastNumber = Complaint::withTrashed()
            ->where('complaint_number', 'like', "{$prefix}-{$date}-%")
            ->orderByRaw("CAST(RIGHT(complaint_number, 4) AS INTEGER) DESC")
            ->value('complaint_number');

        $sequence = $lastNumber ? ((int) substr($lastNumber, -4)) + 1 : 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    /**
     * Calculate average completion time
     */
    private function calculateAverageCompletionTime($startDate, $endDate)
    {
        $completedTickets = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->whereNotNull('actual_completion_date')
            ->get();

        if ($completedTickets->isEmpty()) {
            return 0;
        }

        $totalMinutes = $completedTickets->sum(function($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->actual_completion_date);
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