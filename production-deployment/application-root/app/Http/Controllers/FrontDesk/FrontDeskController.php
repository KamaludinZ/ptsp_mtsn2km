<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use App\Models\Ticket;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FrontDeskController extends Controller
{
    public function index()
    {
        return view('frontdesk.dashboard');
    }

    // Triage - visitor identification
    public function triage()
    {
        return view('frontdesk.triage');
    }

    // Handle visitor check-in
    public function checkInForm()
    {
        return view('frontdesk.visitor-checkin');
    }

    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'purpose' => 'required|string',
            'person_to_meet' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create visitor record
        $visitor = Visitor::create([
            'name' => $request->name,
            'institution' => $request->institution,
            'purpose' => $request->purpose,
            'person_to_meet' => $request->person_to_meet,
            'check_in_time' => now(),
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        // In a real application, you would capture a photo using webcam
        // For now, we'll just create a visitor card number
        $visitor->visitor_card_number = 'VC-' . date('Ymd') . '-' . str_pad($visitor->id, 4, '0', STR_PAD_LEFT);
        $visitor->save();

        return redirect()->route('frontdesk.visitor.checkin.success', ['id' => $visitor->id])
            ->with('success', 'Visitor check-in successful!');
    }

    public function checkInSuccess($id)
    {
        $visitor = Visitor::findOrFail($id);
        return view('frontdesk.visitor-checkin-success', compact('visitor'));
    }

    // Check out visitor
    public function checkOutForm()
    {
        return view('frontdesk.visitor-checkout');
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'visitor_card_number' => 'required|string|exists:visitors,visitor_card_number',
        ]);

        $visitor = Visitor::where('visitor_card_number', $request->visitor_card_number)
            ->where('status', 'active')
            ->first();

        if (!$visitor) {
            return redirect()->back()
                ->with('error', 'Visitor not found or already checked out');
        }

        $visitor->update([
            'check_out_time' => now(),
            'status' => 'checked_out',
        ]);

        return redirect()->route('frontdesk.visitor.checkout.success', ['id' => $visitor->id])
            ->with('success', 'Visitor check-out successful!');
    }

    public function checkOutSuccess($id)
    {
        $visitor = Visitor::findOrFail($id);
        return view('frontdesk.visitor-checkout-success', compact('visitor'));
    }

    // Show active visitors
    public function activeVisitors()
    {
        $visitors = Visitor::where('status', 'active')->get();
        return view('frontdesk.active-visitors', compact('visitors'));
    }

    // Register offline service
    public function registerOfflineServiceForm()
    {
        $services = Service::where('is_active', true)->get();
        $userTypes = [
            'guru' => 'Guru',
            'pegawai' => 'Pegawai',
            'siswa' => 'Siswa',
            'walimurid' => 'Wali Murid',
            'alumni' => 'Alumni',
            'instansi' => 'Instansi',
            'umum' => 'Umum',
        ];

        return view('frontdesk.register-offline-service', compact('services', 'userTypes'));
    }

    public function registerOfflineService(Request $request)
    {
        $request->validate([
            'user_type' => 'required|in:guru,pegawai,siswa,walimurid,alumni,instansi,umum',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'service_id' => 'required|exists:services,id',
            'service_details' => 'required|array',
            'service_details.*' => 'string|max:500',
        ]);

        // Create or find user based on user type
        $user = User::firstOrCreate([
            'email' => $request->email,
        ], [
            'name' => $request->full_name,
            'user_type' => $request->user_type,
            'password' => bcrypt('password'), // Temporary password
            'is_active' => true,
        ]);

        // Create ticket
        $ticket = Ticket::create([
            'ticket_number' => 'LAYANAN-' . date('Ym') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT),
            'user_id' => $user->id,
            'service_id' => $request->service_id,
            'channel' => 'offline',
            'status' => 'submitted',
            'created_by' => Auth::id(), // Front desk staff
        ]);

        // In a real application, we would handle file uploads here
        // For now, we just complete the registration

        return redirect()->route('frontdesk.offline-service.register.success', ['id' => $ticket->id])
            ->with('success', 'Service registration successful! Ticket Number: ' . $ticket->ticket_number);
    }

    public function registerOfflineServiceSuccess($id)
    {
        $ticket = Ticket::with(['service', 'user'])->findOrFail($id);
        return view('frontdesk.register-offline-service-success', compact('ticket'));
    }
}