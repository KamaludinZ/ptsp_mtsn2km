<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\FrontDeskService;
use App\Support\TicketDocuments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Pendaftaran walk-in di loket as JSON: the front desk registers a request for a visiting applicant. */
class WalkInController extends Controller
{
    /** POST /api/loket/permohonan (multipart when berkas[] is sent) */
    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('frontdesk.access'), 403);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::in(array_keys(FrontDeskService::APPLICANT_TYPES))],
            'whatsapp' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'layanan' => ['required', 'string'],
            'keterangan' => ['required', 'string', 'max:1000'],
            'prioritas' => ['nullable', 'in:normal,high,urgent'],
            'berkas' => ['nullable', 'array', 'max:10'],
            'berkas.*' => ['file', 'max:10240', 'mimes:' . TicketDocuments::MIMES],
        ]);

        $service = Service::query()->where('slug', $data['layanan'])->requestable($data['kategori'], 'offline')->first();

        if (! $service) {
            return response()->json(['message' => 'Layanan tidak tersedia di loket untuk kategori pemohon ini.', 'errors' => ['layanan' => ['Layanan tidak tersedia.']]], 422);
        }

        $files = collect($request->file('berkas', []))
            ->mapWithKeys(fn ($upload) => [TicketDocuments::store($upload, 'ticket-files') => $upload->getClientOriginalName()])
            ->all();

        $ticket = app(FrontDeskService::class)->registerWalkIn([
            'service_id' => $service->id,
            'applicant_name' => $data['nama'],
            'applicant_type' => $data['kategori'],
            'applicant_phone' => $data['whatsapp'],
            'applicant_email' => $data['email'] ?? null,
            'description' => $data['keterangan'],
            'priority' => $data['prioritas'] ?? 'normal',
        ], $files, $request->user());

        return response()->json([
            'nomor_tiket' => $ticket->ticket_number,
            'status' => $ticket->status,
            'pemohon' => $ticket->user?->name,
            'target_selesai' => $ticket->estimated_completion_date?->toDateString(),
            'tanda_terima' => route('tickets.receipt', $ticket),
        ], 201);
    }
}
