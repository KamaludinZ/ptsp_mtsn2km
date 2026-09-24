<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\TicketFile;
use App\Models\TicketOutput;
use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackOfficeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:backoffice.access');
    }

    /**
     * Back office dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();

        $ticketCounts = Ticket::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total_tickets' => $ticketCounts->sum(),
            'pending_tickets' => $ticketCounts->get('submitted', 0),
            'in_progress_tickets' => $ticketCounts->get('in_process', 0),
            'completed_tickets' => $ticketCounts->get('completed', 0),
            'my_tickets' => Ticket::where('assigned_to_id', $user->id)->count(),
        ];

        // Recent tickets
        $recentTickets = Ticket::with(['user', 'service', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Tickets by status
        $ticketsByStatus = Ticket::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // Tickets by service (top 5)
        $ticketsByService = Ticket::select('service_id', DB::raw('count(*) as count'))
            ->with('service:id,name')
            ->groupBy('service_id')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $tickets = $recentTickets;

        return view('backoffice.dashboard', compact(
            'stats',
            'recentTickets',
            'tickets',
            'ticketsByStatus',
            'ticketsByService'
        ));
    }

    /**
     * Tickets queue
     */
    public function ticketsQueue()
    {
        $tickets = Ticket::with(['user', 'service', 'assignedTo'])
            ->whereIn('status', ['submitted', 'verified', 'in_process'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('backoffice.tickets-queue', compact('tickets'));
    }

    /**
     * My assigned tickets
     */
    public function myTickets()
    {
        $user = Auth::user();

        $tickets = Ticket::with(['user', 'service'])
            ->where('assigned_to_id', $user->id)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('backoffice.my-tickets', compact('tickets'));
    }

    /**
     * All tickets
     */
    public function allTickets()
    {
        $tickets = Ticket::with(['user', 'service', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('backoffice.all-tickets', compact('tickets'));
    }

    /**
     * Ticket detail
     */
    public function ticketDetail($ticketNumber)
    {
        $ticket = Ticket::with([
            'user',
            'service',
            'assignedTo',
            'files',
            'output',
            'logs' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'workflowSteps' => function($query) {
                $query->with('workflowStep')->orderBy('id');
            }
        ])
        ->where('ticket_number', $ticketNumber)
        ->firstOrFail();

        $availableStaff = User::where('is_active', true)
            ->whereIn('user_type', ['guru', 'pegawai'])
            ->orderBy('name')
            ->get(['id', 'name', 'user_type']);

        return view('backoffice.ticket-detail', compact('ticket', 'availableStaff'));
    }

    /**
     * Assign ticket to staff
     */
    public function assignTicket(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $staff = User::findOrFail($validated['assigned_to']);

        $ticket->update([
            'assigned_to_id' => $staff->id,
            'current_handler_id' => $staff->id,
            'status' => 'in_process',
        ]);

        // Log assignment
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'assigned',
            'notes' => "Tiket ditugaskan kepada {$staff->name}. " . ($validated['notes'] ?? ''),
            'performed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Tiket berhasil ditugaskan.');
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:submitted,verified,in_process,approved,rejected,completed,cancelled',
            'notes' => 'required|string|max:1000',
        ]);

        $oldStatus = $ticket->status;
        $ticket->update([
            'status' => $validated['status'],
        ]);

        // Handle completed status
        if ($validated['status'] === 'completed') {
            $ticket->update(['actual_completion_date' => now()]);
        }

        // Log status change
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'status_changed',
            'from_status' => $oldStatus,
            'to_status' => $validated['status'],
            'notes' => $validated['notes'],
            'performed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Status tiket berhasil diperbarui.');
    }

    /**
     * Add note to ticket
     */
    public function addNote(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'note_added',
            'notes' => $validated['note'],
            'performed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Catatan berhasil ditambahkan.');
    }

    /**
     * Upload file to ticket
     */
    public function uploadFile(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
            'description' => 'nullable|string|max:255',
        ]);

        $file = $validated['file'];
        $path = \App\Support\TicketDocuments::store($file, 'ticket-files');

        TicketFile::create([
            'ticket_id' => $ticket->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'uploaded_by' => Auth::id(),
        ]);

        // Log file upload
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'file_uploaded',
            'notes' => "File {$file->getClientOriginalName()} diunggah. " . ($validated['description'] ?? ''),
            'performed_by' => Auth::id(),
        ]);

        return back()->with('success', 'File berhasil diunggah.');
    }

    /**
     * Download ticket file
     */
    public function downloadFile(Ticket $ticket, TicketFile $file)
    {
        abort_unless($file->ticket_id === $ticket->id, 404);
        $this->authorize('view', $ticket);

        return \App\Support\TicketDocuments::download($file->file_path, $file->file_name);
    }

    /**
     * Upload output file
     */
    public function uploadOutput(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'output_file' => 'required|file|max:20480|mimes:' . \App\Support\TicketDocuments::MIMES,
            'output_description' => 'nullable|string|max:500',
        ]);

        $file = $validated['output_file'];
        $path = \App\Support\TicketDocuments::store($file, 'ticket-outputs');

        // Delete old output if exists
        if ($ticket->output) {
            \App\Support\TicketDocuments::delete($ticket->output->file_path);
            $ticket->output->delete();
        }

        $output = TicketOutput::create([
            'ticket_id' => $ticket->id,
            'output_type' => $file->getClientOriginalExtension(),
            'file_path' => $path,
            'output_description' => $validated['output_description'] ?? null,
        ]);

        // Update ticket status if not completed
        if ($ticket->status !== 'completed') {
            $ticket->update([
                'status' => 'completed',
                'actual_completion_date' => now(),
            ]);
        }

        // Log output upload
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'output_uploaded',
            'notes' => "Output {$file->getClientOriginalName()} diunggah.",
            'performed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Output berhasil diunggah.');
    }

    /**
     * Download ticket output
     */
    public function downloadOutput(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        return \App\Support\TicketDocuments::download(optional($ticket->output)->file_path);
    }

    /**
     * Complete workflow step
     */
    public function completeWorkflowStep(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'step_id' => 'required|exists:workflow_steps,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $step = $ticket->workflowSteps()
            ->where('workflow_step_id', $validated['step_id'])
            ->with('workflowStep')
            ->firstOrFail();

        $step->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $validated['notes'] ?? null,
            'completed_by' => Auth::id(),
        ]);

        // Log step completion
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'workflow_step_completed',
            'notes' => "Langkah workflow '{$step->workflowStep->name}' diselesaikan. " . ($validated['notes'] ?? ''),
            'performed_by' => Auth::id(),
        ]);

        // Check if all steps are completed
        $totalSteps = $ticket->service->workflow->steps()->count();
        $completedSteps = $ticket->workflowSteps()->whereNotNull('completed_at')->count();

        if ($completedSteps === $totalSteps) {
            $ticket->update(['status' => 'completed', 'actual_completion_date' => now()]);
            $ticket->ticketWorkflows()->latest()->first()?->update(['completed_at' => now()]);

            TicketLog::create([
                'ticket_id' => $ticket->id,
                'action' => 'workflow_completed',
                'notes' => 'Seluruh workflow telah diselesaikan.',
                'performed_by' => Auth::id(),
            ]);
        }

        return back()->with('success', 'Langkah workflow berhasil diselesaikan.');
    }

    /**
     * Search tickets
     */
    public function searchTickets(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $serviceId = $request->get('service_id');

        $tickets = Ticket::with(['user', 'service', 'assignedTo'])
            ->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('ticket_number', 'ilike', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'ilike', "%{$search}%")
                                   ->orWhere('email', 'ilike', "%{$search}%");
                      });
                });
            })
            ->when($status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($serviceId, function($query, $serviceId) {
                $query->where('service_id', $serviceId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('backoffice.search-tickets', compact('tickets'));
    }

    /**
     * Reports dashboard
     */
    public function reports()
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

        // Get report data
        $reportData = [
            'total_tickets' => Ticket::whereBetween('created_at', [$startDate, $endDate])->count(),
            'completed_tickets' => Ticket::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')->count(),
            'average_completion_time' => $this->calculateAverageCompletionTime($startDate, $endDate),
            'tickets_by_service' => Ticket::select('service_id', DB::raw('count(*) as count'))
                ->with('service:id,name')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('service_id')
                ->orderBy('count', 'desc')
                ->get(),
            'tickets_by_status' => Ticket::select('status', DB::raw('count(*) as count'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('status')
                ->get(),
        ];

        return view('backoffice.reports', compact('reportData', 'period'));
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
}