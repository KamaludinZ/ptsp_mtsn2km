<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Ticket;
use App\Models\TicketFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
            ->where(function($query) use ($userType) {
                $query->whereJsonContains('user_types_allowed', $userType)
                      ->orWhereJsonContains('user_types_allowed', 'umum');
            })
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
            ->with(['category', 'requirements', 'components', 'workflow'])
            ->where(function($query) use ($userType) {
                $query->where('user_types_allowed', 'LIKE', '%'.$userType.'%')
                      ->orWhere('user_types_allowed', 'LIKE', '%umum%');
            })
            ->firstOrFail();

        // Get related services
        $relatedServices = Service::where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->where(function($query) use ($userType) {
                $query->where('user_types_allowed', 'LIKE', '%'.$userType.'%')
                      ->orWhere('user_types_allowed', 'LIKE', '%umum%');
            })
            ->limit(4)
            ->get();

        return view('onlineportal.service-detail', compact('service', 'relatedServices', 'user'));
    }

    /**
     * Show application form
     */
    public function applicationForm($slug)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('message', 'Silakan login terlebih dahulu untuk mengajukan layanan.');
        }

        $service = Service::where('slug', $slug)
            ->with(['requirements', 'components'])
            ->where(function($query) use ($user) {
                $query->where('user_types_allowed', 'LIKE', '%'.$user->user_type.'%')
                      ->orWhere('user_types_allowed', 'LIKE', '%umum%');
            })
            ->firstOrFail();

        return view('onlineportal.application-form', compact('service', 'user'));
    }

    /**
     * Submit service application
     */
    public function submitApplication(Request $request, $slug)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $service = Service::where('slug', $slug)
            ->where(function($query) use ($user) {
                $query->where('user_types_allowed', 'LIKE', '%'.$user->user_type.'%')
                      ->orWhere('user_types_allowed', 'LIKE', '%umum%');
            })
            ->firstOrFail();

        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'files.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx', // Max 10MB
            'urgency_level' => 'nullable|in:normal,high,urgent',
        ]);

        // Generate ticket number
        $ticketNumber = $this->generateTicketNumber();

        // Create ticket
        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'service_id' => $service->id,
            'user_id' => $user->id,
            'created_by' => $user->id,
            'mode' => 'online',
            'status' => 'submitted',
            'priority' => $validated['urgency_level'] ?? 'normal',
            'notes' => $validated['description'],
        ]);

        // Upload files if any
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('ticket-files', 'public');

                TicketFile::create([
                    'ticket_id' => $ticket->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        // Create initial workflow step if service has workflow
        if ($service->workflow) {
            $firstStep = $service->workflow->steps()->orderBy('order')->first();
            if ($firstStep) {
                $ticketWorkflow = $ticket->ticketWorkflows()->create([
                    'workflow_id' => $service->workflow->id,
                    'current_step_id' => $firstStep->id,
                ]);
                $ticketWorkflow->ticketWorkflowSteps()->create([
                    'workflow_step_id' => $firstStep->id,
                    'status' => 'pending',
                    'notes' => 'Application submitted',
                ]);
            }
        }

        return redirect()->route('onlineportal.application.success', $ticket->ticket_number)
            ->with('success', 'Pengajuan layanan berhasil disimpan dengan nomor tiket: ' . $ticketNumber);
    }

    /**
     * Application success page
     */
    public function applicationSuccess($ticketNumber)
    {
        $ticket = Ticket::with(['service', 'user'])
            ->where('ticket_number', $ticketNumber)
            ->firstOrFail();

        return view('onlineportal.apply-service-success', compact('ticket'));
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

        // Prepare response data
        $responseData = $ticket->toArray();
        
        // Add additional computed fields
        $responseData['has_output_file'] = $ticket->output && $ticket->output->file_path ? true : false;
        
        // Make sure we return the proper structure for the frontend
        return response()->json($responseData);
    }

    /**
     * User dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $tickets = Ticket::with(['service'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $ticketCounts = Ticket::where('user_id', $user->id)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total' => $ticketCounts->sum(),
            'pending' => $ticketCounts->get('submitted', 0),
            'processing' => $ticketCounts->get('verified', 0),
            'in_progress' => $ticketCounts->get('in_process', 0),
            'completed' => $ticketCounts->get('completed', 0),
        ];

        return view('onlineportal.dashboard', compact('user', 'tickets', 'stats'));
    }

    /**
     * My tickets
     */
    public function myTickets()
    {
        $user = Auth::user();

        $tickets = Ticket::with(['service', 'logs' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('onlineportal.my-tickets', compact('tickets'));
    }

    /**
     * Ticket detail
     */
    public function ticketDetail($ticketNumber)
    {
        $user = Auth::user();

        $ticket = Ticket::with([
            'service',
            'user',
            'files',
            'logs' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'workflowSteps' => function($query) {
                $query->with('workflowStep')->orderBy('id');
            }
        ])
        ->where('ticket_number', $ticketNumber)
        ->where('user_id', $user->id)
        ->firstOrFail();

        // Check if can download output
        $canDownload = false;
        if ($ticket->status === 'completed' && $ticket->output) {
            $canDownload = true;
        }

        return view('onlineportal.ticket-detail', compact('ticket', 'canDownload'));
    }

    /**
     * Download ticket output
     */
    public function downloadOutput($ticketNumber)
    {
        $user = Auth::user();

        $ticket = Ticket::with(['output'])
            ->where('ticket_number', $ticketNumber)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->firstOrFail();

        if (!$ticket->output || !$ticket->output->file_path) {
            abort(404, 'File tidak ditemukan');
        }

        $filePath = storage_path('app/public/' . $ticket->output->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }

        return response()->download($filePath, $ticket->output->file_name);
    }

    /**
     * Generate unique ticket number
     */
    private function generateTicketNumber()
    {
        $prefix = 'PTSP';
        $date = Carbon::now()->format('Ym');

        $lastNumber = Ticket::withTrashed()
            ->where('ticket_number', 'like', "{$prefix}-{$date}-%")
            ->orderByRaw("CAST(RIGHT(ticket_number, 4) AS INTEGER) DESC")
            ->value('ticket_number');

        $sequence = $lastNumber ? ((int) substr($lastNumber, -4)) + 1 : 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }
}