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

        try {
            $categories = ServiceCategory::orderBy('name')->get();

            // Services allowed for this user type (user_types_allowed is a jsonb array)
            $services = Service::where('is_active', true)
                ->availableFor($userType)
                ->with(['categories'])
                ->orderBy('name')
                ->get();
            $loadError = false;
        } catch (\Illuminate\Database\QueryException $e) {
            // Show a friendly "gagal dimuat, coba lagi" instead of an error page.
            report($e);
            [$categories, $services, $loadError] = [collect(), collect(), true];
        }

        return response()->view('onlineportal.service-catalog', compact('categories', 'services', 'user', 'loadError'), $loadError ? 503 : 200);
    }

    /**
     * Display service detail
     */
    public function serviceDetail($slug)
    {
        $user = Auth::user();
        $userType = $user ? $user->user_type : 'umum';

        $service = Service::where('slug', $slug)
            ->with(['categories', 'components', 'activeTemplates'])
            ->availableFor($userType)
            ->firstOrFail();

        return view('onlineportal.service-detail', compact('service', 'user'));
    }

    /** A template berkas of an active service: public, like the service page itself (inactive ones for staff only). */
    public function downloadTemplate(string $slug, ServiceTemplate $template)
    {
        $service = Service::where('slug', $slug)->where('is_active', true)->firstOrFail();
        abort_unless($template->service_id === $service->id && ($template->is_active || (bool) auth()->user()?->isStaff()) && Storage::disk(ServiceTemplate::DISK)->exists($template->file_path), 404);

        return Storage::disk(ServiceTemplate::DISK)->download($template->file_path, $template->file_name ?: basename($template->file_path));
    }

    /** Every available template of an active service in one ZIP. */
    public function downloadTemplates(string $slug)
    {
        $service = Service::where('slug', $slug)->where('is_active', true)->with('activeTemplates')->firstOrFail();
        $templates = $service->activeTemplates->filter(fn (ServiceTemplate $template) => $template->isAvailable());
        abort_if($templates->isEmpty(), 404);

        $zipPath = tempnam(sys_get_temp_dir(), 'tpl');
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $used = [];
        foreach ($templates as $template) {
            $name = $template->file_name ?: basename($template->file_path);
            $base = pathinfo($name, PATHINFO_FILENAME);
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            for ($i = 2; isset($used[mb_strtolower($name)]); $i++) {
                $name = "{$base} ({$i})" . ($extension ? ".{$extension}" : '');
            }
            $used[mb_strtolower($name)] = true;
            $zip->addFromString($name, Storage::disk(ServiceTemplate::DISK)->get($template->file_path));
        }
        $zip->close();

        return response()->download($zipPath, 'template-' . \Illuminate\Support\Str::slug($service->name) . '.zip', ['Content-Type' => 'application/zip'])->deleteFileAfterSend();
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