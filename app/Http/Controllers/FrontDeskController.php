<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TicketFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FrontDeskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:frontdesk.access');
    }

    /**
     * Front desk dashboard
     */
    public function dashboard()
    {
        $today = Carbon::today();

        $stats = [
            'visitors_today' => Visitor::whereDate('created_at', $today)->count(),
            'active_visitors' => Visitor::whereDate('created_at', $today)
                ->whereNull('check_out_time')->count(),
            'tickets_today' => Ticket::whereDate('created_at', $today)->count(),
            'pending_tickets' => Ticket::where('status', 'submitted')->count(),
        ];

        $recentVisitors = Visitor::with('staff')
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentTickets = Ticket::with(['user', 'service'])
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('frontdesk.dashboard', compact('stats', 'recentVisitors', 'recentTickets'));
    }

    /**
     * Triage form
     */
    public function triage()
    {
        $staffUsers = User::where('is_active', true)
            ->whereIn('user_type', ['guru', 'pegawai'])
            ->orderBy('name')
            ->get(['id', 'name', 'user_type']);

        return view('frontdesk.triage', compact('staffUsers'));
    }

    /**
     * Process triage
     */
    public function processTriage(Request $request)
    {
        $validated = $request->validate([
            'visitor_type' => 'required|in:guest,applicant',
            'visitor_name' => 'required_if:visitor_type,guest|string|max:255',
            'visitor_email' => 'nullable|email|max:255',
            'visitor_phone' => 'nullable|string|max:20',
            'visitor_institution' => 'nullable|string|max:255',
            'purpose' => 'required|string|max:500',
            'target_user_id' => 'required_if:visitor_type,guest|exists:users,id',
            'visitor_photo' => 'nullable|image|max:2048',
        ]);

        if ($validated['visitor_type'] === 'guest') {
            $targetUser = User::find($validated['target_user_id']);

            // Create visitor record
            $visitor = Visitor::create([
                'name' => $validated['visitor_name'],
                'email' => $validated['visitor_email'] ?? null,
                'phone' => $validated['visitor_phone'] ?? null,
                'institution' => $validated['visitor_institution'] ?? '-',
                'purpose' => $validated['purpose'],
                'person_to_meet' => $targetUser->name ?? null,
                'check_in_time' => now(),
                'created_by' => Auth::id(),
                'status' => 'active',
            ]);

            // Handle photo upload
            if ($request->hasFile('visitor_photo')) {
                $photoPath = $request->file('visitor_photo')->store('visitor-photos', 'public');
                $visitor->update(['photo_path' => $photoPath]);
            }

            // Send visitor pass (implementation depends on email/WhatsApp integration)
            $this->sendVisitorPass($visitor);

            return redirect()->route('frontdesk.triage')
                ->with('success', 'Tamu berhasil didaftarkan. Kartu tamu telah dikirim.')
                ->with('visitor_id', $visitor->id);

        } else {
            // Redirect to service application form for walk-in applicants
            return redirect()->route('frontdesk.service.application')
                ->withInput($request->only(['purpose']));
        }
    }

    /**
     * Walk-in service application form
     */
    public function serviceApplication()
    {
        $user = Auth::user();
        $userType = 'umum'; // Walk-in treated as general public

        $services = Service::where('is_active', true)
            ->availableFor($userType)
            ->with(['categories', 'requirements'])
            ->orderBy('name')
            ->get();

        return view('frontdesk.service-application', compact('services', 'user'));
    }

    /**
     * Submit walk-in service application
     */
    public function submitServiceApplication(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'applicant_name' => 'required|string|max:255',
            'applicant_email' => 'nullable|email|max:255',
            'applicant_phone' => 'required|string|max:20',
            'applicant_type' => 'required|in:guru,pegawai,siswa,walimurid,alumni,instansi,umum',
            'description' => 'required|string|max:1000',
            'files.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
            'urgency_level' => 'nullable|in:normal,high,urgent',
        ]);

        $service = Service::findOrFail($validated['service_id']);

        // Create or find user
        $user = $this->findOrCreateUser($validated);

        // Generate ticket number
        $ticketNumber = $this->generateTicketNumber();

        // Create ticket
        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'service_id' => $service->id,
            'user_id' => $user->id,
            'created_by' => Auth::id(),
            'current_handler_id' => Auth::id(),
            'mode' => 'offline',
            'status' => 'submitted',
            'priority' => $validated['urgency_level'] ?? 'normal',
            'notes' => $validated['description'],
        ]);

        // Upload files
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = \App\Support\TicketDocuments::store($file, 'ticket-files');

                TicketFile::create([
                    'ticket_id' => $ticket->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('frontdesk.service.success', $ticket->ticket_number)
            ->with('success', 'Pengajuan layanan berhasil dengan nomor tiket: ' . $ticketNumber);
    }

    /**
     * Service application success
     */
    public function serviceSuccess($ticketNumber)
    {
        $ticket = Ticket::with(['service', 'user'])
            ->where('ticket_number', $ticketNumber)
            ->firstOrFail();

        return view('frontdesk.register-offline-service-success', compact('ticket'));
    }

    /**
     * Visitor book
     */
    public function visitorBook()
    {
        $date = request('date', Carbon::today()->toDateString());

        $visitors = Visitor::with('staff')
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('frontdesk.visitor-book', compact('visitors', 'date'));
    }

    /**
     * Check out visitor
     */
    public function checkoutVisitor(Visitor $visitor)
    {
        if ($visitor->check_out_time) {
            return back()->with('error', 'Tamu sudah check out.');
        }

        $visitor->update([
            'check_out_time' => now(),
        ]);

        return back()->with('success', 'Tamu berhasil check out.');
    }

    /**
     * Print visitor pass
     */
    public function printVisitorPass(Visitor $visitor)
    {
        return view('frontdesk.print-visitor-pass', compact('visitor'));
    }

    /**
     * Active visitors list
     */
    public function activeVisitors()
    {
        $activeVisitors = Visitor::with('staff')
            ->whereNull('check_out_time')
            ->orderBy('created_at', 'desc')
            ->get();

        $visitors = $activeVisitors;

        return view('frontdesk.active-visitors', compact('activeVisitors', 'visitors'));
    }

    /**
     * Search visitors
     */
    public function searchVisitors(Request $request)
    {
        $search = $request->get('search');

        $visitors = Visitor::with('staff')
            ->where(function($query) use ($search) {
                $query->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%")
                    ->orWhere('institution', 'ilike', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json($visitors);
    }

    /**
     * Find or create user for walk-in application
     */
    private function findOrCreateUser($data)
    {
        // Try to find existing user by email or WhatsApp number
        $user = User::where(function($query) use ($data) {
                if (!empty($data['applicant_email'])) {
                    $query->where('email', $data['applicant_email']);
                }
                if (!empty($data['applicant_phone'])) {
                    $query->orWhere('whatsapp_number', $data['applicant_phone']);
                }
            })
            ->first();

        if (!$user) {
            // email is NOT NULL/unique on users; walk-in applicants aren't
            // always able to give one, so synthesize one from their phone.
            $email = $data['applicant_email'] ?? ($data['applicant_phone'] . '@walkin.local');

            // Create new user
            $user = User::create([
                'name' => $data['applicant_name'],
                'email' => $email,
                'whatsapp_number' => $data['applicant_phone'] ?? null,
                'user_type' => $data['applicant_type'],
                'password' => bcrypt(str()->random(10)), // Random password
                'is_active' => true,
            ]);
        }

        return $user;
    }

    /**
     * Send visitor pass (placeholder for email/WhatsApp integration)
     */
    private function sendVisitorPass($visitor)
    {
        // TODO: Implement email/WhatsApp notification
        // This would send the visitor pass with QR code or access details
        \Log::info('Visitor pass sent for visitor: ' . $visitor->id);
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