<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceTemplate;
use App\Models\Ticket;
use App\Support\TicketTracking;
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

        $ticket = TicketTracking::find($validated['ticket_number']);

        // A wrong e-mail is treated like a wrong number: nothing to tell.
        if (! $ticket || (filled($validated['email'] ?? null) && ! TicketTracking::isOwner($ticket, $validated['email']))) {
            return response()->json(['message' => 'Nomor tiket tidak ditemukan'], 404);
        }

        return response()->json(TicketTracking::summary($ticket, TicketTracking::isOwner($ticket, $validated['email'] ?? null)));
    }

}