<?php

namespace App\Http\Controllers\Supervision;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\SurveyQuestion;
use App\Models\SurveyAnswer;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupervisionController extends Controller
{
    // Show complaints dashboard
    public function complaintsDashboard()
    {
        $user = Auth::user();
        
        // Get complaints based on user role
        if ($user->hasRole(['admin', 'kepala-tu', 'kepala-sekolah'])) {
            $complaints = Complaint::with(['user', 'assignee'])->latest()->get();
        } else {
            $complaints = Complaint::where('assigned_to', $user->id)
                ->orWhere('user_id', $user->id)
                ->with(['user', 'assignee'])
                ->latest()
                ->get();
        }

        return view('supervision.complaints-dashboard', compact('complaints'));
    }

    // Show create complaint form
    public function createComplaintForm()
    {
        return view('supervision.create-complaint-form');
    }

    // Submit a complaint
    public function submitComplaint(Request $request)
    {
        $request->validate([
            'complaint_type' => 'required|in:complaint,suggestion,whistleblowing',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'anonymous' => 'boolean',
            'complainant_name' => 'required_unless:anonymous,true|nullable|string|max:255',
            'complainant_email' => 'required_unless:anonymous,true|nullable|email|max:255',
            'complainant_contact' => 'nullable|string|max:255',
        ]);

        $complaint = Complaint::create([
            'complaint_type' => $request->complaint_type,
            'title' => $request->title,
            'description' => $request->description,
            'anonymous' => $request->boolean('anonymous'),
            'complainant_name' => $request->complainant_name,
            'complainant_email' => $request->complainant_email,
            'complainant_contact' => $request->complainant_contact,
            'user_id' => Auth::check() ? Auth::id() : null,
            'status' => 'submitted',
        ]);

        return redirect()->route('supervision.complaint.submitted.success', ['id' => $complaint->id])
            ->with('success', 'Your complaint has been submitted successfully!');
    }

    // Show complaint submitted success
    public function complaintSubmittedSuccess($id)
    {
        $complaint = Complaint::findOrFail($id);
        return view('supervision.complaint-submitted-success', compact('complaint'));
    }

    // Process a complaint
    public function processComplaint($id)
    {
        $complaint = Complaint::with(['user', 'resolver', 'assignee'])->findOrFail($id);
        $users = \App\Models\User::role(['admin', 'tu', 'kepala-tu', 'kepala-sekolah'])->get();
        
        return view('supervision.process-complaint', compact('complaint', 'users'));
    }

    // Update complaint status
    public function updateComplaint(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:in_review,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $complaint = Complaint::findOrFail($id);
        
        $updateData = [
            'status' => $request->status,
            'resolution_notes' => $request->resolution_notes,
        ];
        
        if ($request->filled('assigned_to')) {
            $updateData['assigned_to'] = $request->assigned_to;
        }
        
        // If status is resolved, set resolved_by and resolved_at
        if ($request->status === 'resolved') {
            $updateData['resolved_by'] = Auth::id();
            $updateData['resolved_at'] = now();
        }
        
        $complaint->update($updateData);

        return redirect()->route('supervision.complaints.dashboard')
            ->with('success', 'Complaint updated successfully!');
    }

    // Show all surveys
    public function surveysDashboard()
    {
        $surveys = Survey::with(['questions'])->get();
        return view('supervision.surveys-dashboard', compact('surveys'));
    }

    // Show survey form
    public function showSurveyForm($id)
    {
        $survey = Survey::with('questions')->findOrFail($id);
        
        // Check if survey is active and within date range
        if (!$survey->is_active || 
            (now()->lt($survey->start_date) || (isset($survey->end_date) && now()->gt($survey->end_date)))) {
            return redirect()->route('supervision.surveys.dashboard')
                ->with('error', 'This survey is not available at this time.');
        }

        return view('supervision.survey-form', compact('survey'));
    }

    // Submit survey response
    public function submitSurvey(Request $request, $id)
    {
        $survey = Survey::findOrFail($id);
        
        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|string|max:1000',
        ]);

        // Create survey response
        $response = SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => Auth::check() ? Auth::id() : null,
            'completed_at' => now(),
        ]);

        // Create answers
        foreach ($request->answers as $question_id => $answer) {
            $response->answers()->create([
                'survey_question_id' => $question_id,
                'answer_text' => $answer,
            ]);
        }

        return redirect()->route('supervision.survey.completed', ['id' => $survey->id])
            ->with('success', 'Thank you for completing the survey!');
    }

    // Show survey completed page
    public function surveyCompleted($id)
    {
        $survey = Survey::findOrFail($id);
        return view('supervision.survey-completed', compact('survey'));
    }

    // Show SKM (Survey Kepuasan Masyarakat) form after ticket completion
    public function showSKMAfterTicket($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        
        // Check if ticket is completed
        if ($ticket->status !== 'completed') {
            return redirect()->route('onlineportal.my.services')
                ->with('error', 'Survey can only be taken for completed tickets.');
        }

        $survey = Survey::where('type', 'skm')->where('is_active', true)->first();
        
        if (!$survey) {
            return redirect()->route('onlineportal.my.services')
                ->with('error', 'SKM survey is not available at this time.');
        }

        return view('supervision.skm-form', compact('ticket', 'survey'));
    }
    
    // Submit SKM response
    public function submitSKM(Request $request, $ticketId, $surveyId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $survey = Survey::findOrFail($surveyId);
        
        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|string|max:1000',
        ]);

        // Check if user already completed the survey for this ticket
        $existingResponse = SurveyResponse::where('survey_id', $survey->id)
            ->where('ticket_id', $ticketId)
            ->where('user_id', Auth::id())
            ->first();
            
        if ($existingResponse) {
            return redirect()->route('onlineportal.my.services')
                ->with('error', 'You have already completed this survey for this ticket.');
        }

        // Create survey response
        $response = SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => Auth::id(),
            'ticket_id' => $ticket->id,
            'completed_at' => now(),
        ]);

        // Create answers
        foreach ($request->answers as $question_id => $answer) {
            $response->answers()->create([
                'survey_question_id' => $question_id,
                'answer_text' => $answer,
            ]);
        }

        return redirect()->route('onlineportal.my.services')
            ->with('success', 'Thank you for completing the SKM survey!');
    }

    // Show SPAK (Survey Persepsi Anti Korupsi) form
    public function showSPAKForm()
    {
        $survey = Survey::where('type', 'spak')->where('is_active', true)->first();
        
        if (!$survey) {
            return redirect()->route('supervision.surveys.dashboard')
                ->with('error', 'SPAK survey is not available at this time.');
        }

        return view('supervision.spak-form', compact('survey'));
    }
}