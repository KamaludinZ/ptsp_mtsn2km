<?php

namespace App\Http\Controllers\OnlinePortal;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceComponent;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class OnlinePortalController extends Controller
{
    // Show the service catalog
    public function serviceCatalog()
    {
        $user = Auth::user();
        $services = Service::where('is_active', true)
            ->when($user, function ($query, $user) {
                // Only show services allowed for the user's type
                return $query->whereJsonContains('user_types_allowed', $user->user_type);
            }, function ($query) {
                // For non-authenticated users, show services available to umum
                return $query->whereJsonContains('user_types_allowed', 'umum');
            })
            ->get();

        return view('onlineportal.service-catalog', compact('services'));
    }

    // Show service details
    public function serviceDetails($id)
    {
        $service = Service::with(['requirements', 'components'])->findOrFail($id);
        $components = $service->components->keyBy('component_type');
        
        return view('onlineportal.service-details', compact('service', 'components'));
    }

    // Form to apply for a service online
    public function applyForServiceForm($id)
    {
        $service = Service::findOrFail($id);
        $requirements = $service->requirements;
        
        return view('onlineportal.apply-service-form', compact('service', 'requirements'));
    }

    // Process the service application
    public function applyForService(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        
        $request->validate([
            'service_details' => 'required|string|max:1000',
            'files' => 'array',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB per file
        ]);

        // Create ticket
        $ticket = Ticket::create([
            'ticket_number' => 'LAYANAN-' . date('Ym') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT),
            'user_id' => Auth::id(),
            'service_id' => $service->id,
            'channel' => 'online',
            'status' => 'submitted',
            'created_by' => Auth::id(),
        ]);

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('ticket_files', 'public');
                
                $ticket->files()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('onlineportal.apply.service.success', ['id' => $ticket->id])
            ->with('success', 'Service application submitted successfully! Ticket Number: ' . $ticket->ticket_number);
    }

    // Service application success page
    public function applyForServiceSuccess($id)
    {
        $ticket = Ticket::with(['service', 'user'])->findOrFail($id);
        return view('onlineportal.apply-service-success', compact('ticket'));
    }

    // Show user's tickets/services
    public function myServices()
    {
        $tickets = Ticket::where('user_id', Auth::id())
            ->with(['service', 'logs'])
            ->latest()
            ->get();

        return view('onlineportal.my-services', compact('tickets'));
    }

    // Track ticket status
    public function trackTicketForm()
    {
        return view('onlineportal.track-ticket-form');
    }

    public function trackTicket(Request $request)
    {
        $request->validate([
            'ticket_number' => 'required|string|exists:tickets,ticket_number',
        ]);

        $ticket = Ticket::with(['service', 'user', 'logs', 'outputs'])
            ->where('ticket_number', $request->ticket_number)
            ->first();

        // Check if the user can view this ticket (either they own it or they know the ticket number)
        if (!$ticket) {
            return redirect()->back()->with('error', 'Ticket not found');
        }

        return view('onlineportal.track-ticket-result', compact('ticket'));
    }

    // User registration form for internal users
    public function internalRegistrationForm()
    {
        return view('onlineportal.internal-registration-form');
    }

    public function internalRegistration(Request $request)
    {
        $request->validate([
            'registration_code' => 'required|string|exists:users,registration_code',
            'user_type' => 'required|in:guru,pegawai,siswa,walimurid,alumni,instansi,umum',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Verify registration code belongs to a user
        $codeUser = User::where('registration_code', $request->registration_code)->first();
        
        if (!$codeUser) {
            return redirect()->back()->with('error', 'Invalid registration code')->withInput();
        }

        // Validate if the user type is appropriate based on their registration code
        // For this example, we'll allow any valid internal user type
        if (!in_array($request->user_type, ['guru', 'pegawai', 'siswa', 'walimurid', 'alumni'])) {
            return redirect()->back()->with('error', 'Invalid user type for internal registration')->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'registration_code' => $request->registration_code,
            'is_active' => true,
        ]);

        return redirect()->route('login')->with('success', 'Registration successful! Please login.');
    }

    // User registration form for external users
    public function externalRegistrationForm()
    {
        return view('onlineportal.external-registration-form');
    }

    public function externalRegistration(Request $request)
    {
        $request->validate([
            'user_type' => 'required|in:walimurid,alumni,instansi,umum',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'is_active' => true,
        ]);

        return redirect()->route('login')->with('success', 'Registration successful! Please login.');
    }
}