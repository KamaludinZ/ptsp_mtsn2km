<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceTemplate;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OnlinePortalController extends Controller
{
    /**
     * Display service catalog
     */
    public function serviceCatalog()
    {
        $user = Auth::user();
        $userType = $user ? $user->user_type : 'umum';

        // Get categories
        $categories = ServiceCategory::orderBy('name')->get();

        // Get services allowed for this user type (user_types_allowed is a jsonb array)
        $services = Service::where('is_active', true)
            ->availableFor($userType)
            ->with(['categories'])
            ->orderBy('name')
            ->get();

        return view('onlineportal.service-catalog', compact('categories', 'services', 'user'));
    }

    /**
     * Display service detail
     */
    public function serviceDetail($slug)
    {
        $user = Auth::user();
        $userType = $user ? $user->user_type : 'umum';

        $service = Service::where('slug', $slug)
            ->with(['categories', 'components', 'templates'])
            ->availableFor($userType)
            ->firstOrFail();

        return view('onlineportal.service-detail', compact('service', 'user'));
    }

    /** A template berkas of an active service: public, like the service page itself. */
    public function downloadTemplate(string $slug, ServiceTemplate $template)
    {
        $service = Service::where('slug', $slug)->where('is_active', true)->firstOrFail();
        abort_unless($template->service_id === $service->id && Storage::disk(ServiceTemplate::DISK)->exists($template->file_path), 404);

        return Storage::disk(ServiceTemplate::DISK)->download($template->file_path, $template->file_name ?: basename($template->file_path));
    }

    /**
     * Track ticket form
     */
    public function trackTicketForm()
    {
        return view('onlineportal.track-ticket-form');
    }

    /**
     * Track ticket result
     */
    public function trackTicket(Request $request)
    {
        $validated = $request->validate([
            'ticket_number' => 'required|string',
            'email' => 'nullable|email',
        ]);

        $ticketQuery = Ticket::with([
            'service', 
            'user', 
            'logs', 
            'files',
            'workflowSteps' => function($query) {
                $query->with('workflowStep')->orderBy('id');
            },
            'output' // Include output file info
        ])
        ->where('ticket_number', $validated['ticket_number']);

        // If email provided, verify ownership
        if (!empty($validated['email'])) {
            $ticketQuery->whereHas('user', function($query) use ($validated) {
                $query->where('email', $validated['email']);
            });
        }

        $ticket = $ticketQuery->first();

        if (!$ticket) {
            return response()->json(['message' => 'Nomor tiket tidak ditemukan'], 404);
        }

        // Anyone with the ticket number may track it, so only return progress
        // information — never the applicant's personal data.
        return response()->json([
            'ticket_number' => $ticket->ticket_number,
            'status' => $ticket->status,
            'submitted_at' => optional($ticket->created_at)->toIso8601String(),
            'description' => $ticket->notes,
            'service' => $ticket->service ? [
                'name' => $ticket->service->name,
                'mode' => $ticket->mode,
                'processing_time' => $ticket->service->processing_time,
            ] : null,
            'has_output_file' => (bool) optional($ticket->output)->file_path,
            'logs' => $ticket->logs->sortBy('created_at')->values()->map(fn ($log) => [
                'action' => $log->action,
                'notes' => $log->notes,
                'created_at' => optional($log->created_at)->toIso8601String(),
            ]),
            'workflow_steps' => $ticket->workflowSteps->map(fn ($step) => [
                'name' => optional($step->workflowStep)->name,
                'pivot' => [
                    'completed_at' => optional($step->completed_at)->toIso8601String(),
                    'notes' => $step->notes,
                ],
            ]),
        ]);
    }

}