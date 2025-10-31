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

class OnlinePortalController extends Controller
{
    /**
     * Display service catalog
     */
    public function serviceCatalog()
    {
        $user = Auth::user();
        $userType = $user ? $user->user_type : 'umum';

        // Get categories - all categories for now since we don't have service-category relationship
        $categories = ServiceCategory::orderBy('name')->get();

        // Get services based on user type (using LIKE for SQLite compatibility)
        $services = Service::where('user_types_allowed', 'LIKE', '%"' . $userType . '"%')
            ->where('is_active', true)
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
            ->whereJsonContains('user_types_allowed', $userType)
            ->firstOrFail();

        // Get related services
        $relatedServices = Service::where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->whereJsonContains('user_types_allowed', $userType)
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
            ->whereJsonContains('user_types_allowed', $user->user_type)
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
            ->whereJsonContains('user_types_allowed', $user->user_type)
            ->firstOrFail();

        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'files.*' => 'nullable|file|max:10240', // Max 10MB
            'urgency_level' => 'nullable|in:normal,high,urgent',
        ]);

        // Generate ticket number
        $ticketNumber = $this->generateTicketNumber();

        // Create ticket
        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'service_id' => $service->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'priority' => $validated['urgency_level'] ?? 'normal',
            'description' => $validated['description'],
            'submitted_at' => now(),
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
                $ticket->workflowSteps()->attach($firstStep->id, [
                    'completed_at' => null,
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

        return view('onlineportal.application-success', compact('ticket'));
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

        $ticket = Ticket::with(['service', 'user', 'logs', 'files'])
            ->where('ticket_number', $validated['ticket_number']);

        // If email provided, verify ownership
        if (!empty($validated['email'])) {
            $ticket->whereHas('user', function($query) use ($validated) {
                $query->where('email', $validated['email']);
            });
        }

        $ticket = $ticket->firstOrFail();

        return view('onlineportal.track-ticket-result', compact('ticket'));
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

        $stats = [
            'total' => Ticket::where('user_id', $user->id)->count(),
            'pending' => Ticket::where('user_id', $user->id)->where('status', 'pending')->count(),
            'processing' => Ticket::where('user_id', $user->id)->where('status', 'processing')->count(),
            'completed' => Ticket::where('user_id', $user->id)->where('status', 'completed')->count(),
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
                $query->withPivot('completed_at', 'notes');
                $query->orderBy('order');
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
        $sequence = Ticket::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }
}