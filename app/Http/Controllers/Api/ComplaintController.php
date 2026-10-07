<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\TicketActionException;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use App\Services\ComplaintService;
use App\Support\RoleAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Pengaduan, saran & WBS for complaint handlers. */
class ComplaintController extends Controller
{
    /** GET /api/pengaduan?jenis=&status=&prioritas=&q=&per_halaman= */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Complaint::class);

        $filters = $request->validate([
            'jenis' => ['nullable', Rule::in(array_keys(Complaint::TYPES))],
            'status' => ['nullable', Rule::in(array_keys(Complaint::STATUSES))],
            'prioritas' => ['nullable', Rule::in(array_keys(Complaint::PRIORITIES))],
            'q' => ['nullable', 'string', 'max:100'],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $page = Complaint::query()
            ->when($filters['jenis'] ?? null, fn ($q, $type) => $q->where('complaint_type', $type))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['prioritas'] ?? null, fn ($q, $priority) => $q->where('priority', $priority))
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where(fn ($q) => $q->where('complaint_number', 'ilike', $like)->orWhere('title', 'ilike', $like));
            })
            ->latest()
            ->paginate($filters['per_halaman'] ?? 25)
            ->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (Complaint $complaint) => self::summary($complaint)),
            'halaman' => $page->currentPage(),
            'total' => $page->total(),
            'halaman_terakhir' => $page->lastPage(),
        ]);
    }

    /** PATCH /api/pengaduan/{complaint}: move to another stage and/or reply to the reporter. */
    public function update(Request $request, Complaint $complaint, ComplaintService $complaints): JsonResponse
    {
        $this->authorize('update', $complaint);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Complaint::STATUSES))],
            'prioritas' => ['nullable', Rule::in(array_keys(Complaint::PRIORITIES))],
            'penangan' => ['nullable', Rule::exists(User::class, 'id')->where(fn ($q) => $q->whereIn('id', User::role(RoleAccess::COMPLAINT_HANDLERS)->select('id')))],
            'tanggapan' => ['nullable', 'string', 'max:5000'],
            'catatan_internal' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $complaints->followUp($complaint, [
                'status' => $data['status'],
                'priority' => $data['prioritas'] ?? $complaint->priority ?? 'normal',
                'assigned_to' => array_key_exists('penangan', $data) ? $data['penangan'] : $complaint->assigned_to,
                'response' => $data['tanggapan'] ?? null,
                'resolution_notes' => $data['catatan_internal'] ?? null,
            ], $request->user());
        } catch (TicketActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $complaint->refresh();

        return response()->json(self::summary($complaint) + [
            'tanggapan' => $complaint->response,
            'tahapan' => $complaint->statusLogs()->with('actor:id,name')->get()->map(fn ($log) => [
                'status' => $log->to_status,
                'waktu' => $log->created_at?->toIso8601String(),
                'oleh' => $log->actor?->name,
                'tanggapan' => $log->response,
                'catatan_internal' => $log->internal_note,
            ]),
        ]);
    }

    /**
     * POST /api/pengaduan/{complaint}/rahasiakan {alasan}: hide the reporter's identity
     * from everyone but the handlers (one way). Abusive senders are blocked by IP on
     * the Keamanan page; reports never store the sender's IP, to keep whistleblowers safe.
     */
    public function makeConfidential(Request $request, Complaint $complaint, ComplaintService $complaints): JsonResponse
    {
        $this->authorize('update', $complaint);

        $data = $request->validate(['alasan' => ['required', 'string', 'max:500']]);

        try {
            $complaints->makeConfidential($complaint, $request->user(), $data['alasan']);
        } catch (TicketActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(self::summary($complaint->refresh()) + ['rahasia' => $complaint->isSecret()]);
    }

    public static function summary(Complaint $complaint): array
    {
        return [
            'id' => $complaint->id,
            'nomor' => $complaint->complaint_number,
            'jenis' => $complaint->complaint_type,
            'judul' => $complaint->title,
            'status' => $complaint->status,
            'status_label' => Complaint::STATUSES[$complaint->status] ?? $complaint->status,
            'prioritas' => $complaint->priority,
            'rahasia' => $complaint->isSecret(),
            // Anonymous whistleblowers stay anonymous; secret identities only for handlers.
            'pelapor' => $complaint->reporterLabel(),
            'masuk' => $complaint->created_at?->toIso8601String(),
        ];
    }
}
