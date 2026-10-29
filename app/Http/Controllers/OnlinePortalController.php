<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceTemplate;
use App\Models\Ticket;
use App\Models\TicketOutput;
use App\Services\TicketDownloadAttempts;
use App\Support\ServiceVisitor;
use App\Support\TicketDocuments;
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
        $visitor = ServiceVisitor::current();

        try {
            // Services open to the visitor's type (user_types_allowed is a jsonb array); staff see all.
            $services = $visitor->catalog(Service::query())
                ->with(['categories' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get();

            // Offer every category a listed service belongs to (a service may have
            // several), with how many listed services carry it.
            $categories = $services->flatMap->categories
                ->groupBy('id')
                ->map(fn ($group) => $group->first()->setAttribute('services_count', $group->count()))
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
            $loadError = false;
        } catch (\Illuminate\Database\QueryException $e) {
            // Show a friendly "gagal dimuat, coba lagi" instead of an error page.
            report($e);
            [$categories, $services, $loadError] = [collect(), collect(), true];
        }

        return response()->view('onlineportal.service-catalog', compact('categories', 'services', 'user', 'visitor', 'loadError'), $loadError ? 503 : 200);
    }

    /**
     * Display service detail
     */
    public function serviceDetail($slug)
    {
        $user = Auth::user();
        $visitor = ServiceVisitor::current();

        $service = $visitor->pages(Service::where('slug', $slug))
            ->with(['categories', 'components', 'activeTemplates'])
            ->firstOrFail();

        return view('onlineportal.service-detail', compact('service', 'user', 'visitor'));
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

    /**
     * POST /tracking/unduh: an applicant without an account proves a finished
     * request is theirs with the e-mail or WhatsApp number given on it, and
     * gets a short-lived link to the product file. TicketDownloadAttempts
     * counts the wrong tries and locks the ticket after the last one.
     */
    public function verifyOutputDownload(Request $request, TicketDownloadAttempts $attempts)
    {
        $validated = $request->validate([
            'ticket_number' => 'required|string|max:50',
            'kontak' => 'required|string|max:255',
        ], [], ['ticket_number' => 'nomor tiket', 'kontak' => 'email atau nomor WhatsApp']);

        return $attempts->answer(TicketTracking::find($validated['ticket_number']), $validated['kontak'], $request->ip());
    }

    /**
     * GET /tracking/hasil/{output}: the product file, through the signed link
     * verifyOutputDownload gave. Only the request's current product, while the
     * request is still finished.
     */
    public function downloadOutput(TicketOutput $output)
    {
        $ticket = $output->ticket;
        abort_unless($ticket && $ticket->output?->is($output) && TicketTracking::hasOutputFile($ticket), 404);

        return TicketDocuments::download($output->file_path, TicketDocuments::outputName($ticket));
    }
}
