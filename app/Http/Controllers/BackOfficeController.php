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
        $this->middleware('permission:backoffice-access');
    }

    /**
     * Back office dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();

        $stats = [
            'total_tickets' => Ticket::count(),
            'pending_tickets' => Ticket::where('status', 'pending')->count(),
            'processing_tickets' => Ticket::where('status', 'processing')->count(),
            'completed_tickets' => Ticket::where('status', 'completed')->count(),
            'my_tickets' => Ticket::where('assigned_to', $user->id)->count(),
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

        return view('backoffice.dashboard', compact(
            'stats',
            'recentTickets',
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
            ->whereIn('status', ['pending', 'processing'])
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
            ->where('assigned_to', $user->id)
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
                $query->withPivot('completed_at', 'notes');
                $query->orderBy('order');
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
            'assigned_to' => $staff->id,
            'assigned_at' => now(),
            'status' => 'processing',
        ]);

        // Log assignment
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'assigned',
            'description' => "Tiket ditugaskan kepada {$staff->name}. " . ($validated['notes'] ?? ''),
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
            'status' => 'required|in:pending,processing,completed,rejected,cancelled',
            'notes' => 'required|string|max:1000',
        ]);

        $oldStatus = $ticket->status;
        $ticket->update([
            'status' => $validated['status'],
        ]);

        // Handle completed status
        if ($validated['status'] === 'completed') {
            $ticket->update(['completed_at' => now()]);
        }

        // Log status change
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'status_changed',
            'description' => "Status diubah dari {$oldStatus} menjadi {$validated['status']}. {$validated['notes']}",
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
            'description' => $validated['note'],
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
            'file' => 'required|file|max:10240',
            'description' => 'nullable|string|max:255',
        ]);

        $file = $validated['file'];
        $path = $file->store('ticket-files', 'public');

        TicketFile::create([
            'ticket_id' => $ticket->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $validated['description'] ?? null,
            'uploaded_by' => Auth::id(),
        ]);

        // Log file upload
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'file_uploaded',
            'description' => "File {$file->getClientOriginalName()} diunggah.",
            'performed_by' => Auth::id(),
        ]);

        return back()->with('success', 'File berhasil diunggah.');
    }

    /**
     * Download ticket file
     */
    public function downloadFile(TicketFile $file)
    {
        $this->authorize('view', $file->ticket);

        $filePath = storage_path('app/public/' . $file->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }

        return response()->download($filePath, $file->file_name);
    }

    /**
     * Upload output file
     */
    public function uploadOutput(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'output_file' => 'required|file|max:20480',
            'output_description' => 'nullable|string|max:500',
        ]);

        $file = $validated['output_file'];
        $path = $file->store('ticket-outputs', 'public');

        // Delete old output if exists
        if ($ticket->output) {
            Storage::disk('public')->delete($ticket->output->file_path);
            $ticket->output->delete();
        }

        $output = TicketOutput::create([
            'ticket_id' => $ticket->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $validated['output_description'] ?? null,
            'created_by' => Auth::id(),
        ]);

        // Update ticket status if not completed
        if ($ticket->status !== 'completed') {
            $ticket->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        // Log output upload
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'output_uploaded',
            'description' => "Output {$file->getClientOriginalName()} diunggah.",
            'performed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Output berhasil diunggah.');
    }

    /**
     * Download ticket output
     */
    public function downloadOutput(Ticket $ticket)
    {
        if (!$ticket->output || !$ticket->output->file_path) {
            abort(404, 'File output tidak ditemukan');
        }

        $filePath = storage_path('app/public/' . $ticket->output->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }

        return response()->download($filePath, $ticket->output->file_name);
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
            ->wherePivot('workflow_step_id', $validated['step_id'])
            ->firstOrFail();

        $ticket->workflowSteps()->updateExistingPivot($step->id, [
            'completed_at' => now(),
            'notes' => $validated['notes'],
            'completed_by' => Auth::id(),
        ]);

        // Log step completion
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'workflow_step_completed',
            'description' => "Langkah workflow '{$step->name}' diselesaikan. " . ($validated['notes'] ?? ''),
            'performed_by' => Auth::id(),
        ]);

        // Check if all steps are completed
        $totalSteps = $ticket->service->workflow->steps()->count();
        $completedSteps = $ticket->workflowSteps()->wherePivotNotNull('completed_at')->count();

        if ($completedSteps === $totalSteps) {
            $ticket->update([
                'status' => 'processing',
                'workflow_completed_at' => now(),
            ]);

            TicketLog::create([
                'ticket_id' => $ticket->id,
                'action' => 'workflow_completed',
                'description' => 'Seluruh workflow telah diselesaikan.',
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
                    $q->where('ticket_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
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
}