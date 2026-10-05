<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\TicketActionException;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Support\RoleAccess;
use App\Support\SurveyReminders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Pengingat survei as JSON: completed requests not rated yet, and reminders to their applicants. */
class SurveyReminderController extends Controller
{
    /** GET /api/survei/belum-mengisi?per_halaman= */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(RoleAccess::COMPLAINT_HANDLERS), 403);

        $page = SurveyReminders::unrated()
            ->with(['service:id,name', 'user:id,name'])
            ->latest('actual_completion_date')
            ->paginate((int) ($request->validate(['per_halaman' => ['nullable', 'integer', 'min:1', 'max:100']])['per_halaman'] ?? 25));

        return response()->json([
            'data' => collect($page->items())->map(fn (Ticket $ticket) => [
                'nomor_tiket' => $ticket->ticket_number,
                'layanan' => $ticket->service?->name,
                'pemohon' => $ticket->user?->name,
                'selesai' => $ticket->actual_completion_date?->toDateString(),
                'pengingat_terakhir' => ($at = SurveyReminders::lastSentAt($ticket)) ? Carbon::parse($at)->toIso8601String() : null,
            ])->values(),
            'meta' => ['halaman' => $page->currentPage(), 'per_halaman' => $page->perPage(), 'total' => $page->total()],
        ]);
    }

    /** POST /api/survei/pengingat {tiket: [ticket_number, …]} (max 50) */
    public function send(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(RoleAccess::COMPLAINT_HANDLERS), 403);

        $numbers = $request->validate([
            'tiket' => ['required', 'array', 'min:1', 'max:50'],
            'tiket.*' => ['string', 'distinct'],
        ])['tiket'];

        $tickets = Ticket::whereIn('ticket_number', $numbers)->get()->keyBy('ticket_number');
        $results = collect($numbers)->map(function (string $number) use ($tickets, $request) {
            if (! $ticket = $tickets->get($number)) {
                return ['nomor_tiket' => $number, 'terkirim' => false, 'pesan' => 'Tiket tidak ditemukan.'];
            }

            try {
                $channels = SurveyReminders::remind($ticket, $request->user());
            } catch (TicketActionException $e) {
                return ['nomor_tiket' => $number, 'terkirim' => false, 'pesan' => $e->getMessage()];
            }

            return ['nomor_tiket' => $number, 'terkirim' => (bool) $channels, 'kanal' => $channels, 'pesan' => $channels ? null : 'Tidak ada kanal aktif atau kontak pemohon.'];
        });

        return response()->json(['terkirim' => $results->where('terkirim', true)->count(), 'hasil' => $results->values()]);
    }
}
