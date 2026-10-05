<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\TicketActionException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Visitor;
use App\Services\FrontDeskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Buku tamu for the counter (front desk). */
class VisitorController extends Controller
{
    public function __construct(private FrontDeskService $frontDesk)
    {
    }

    /** GET /api/buku-tamu?tanggal=&dari=&sampai=&q=&status=di_lokasi|keluar&kategori=&per_halaman= */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Visitor::class);

        $filters = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['di_lokasi', 'keluar'])],
            'kategori' => ['nullable', 'string', 'max:255'],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $page = Visitor::query()
            ->when($filters['tanggal'] ?? null, fn ($q, $date) => $q->whereDate('check_in_time', $date))
            ->when($filters['dari'] ?? null, fn ($q, $date) => $q->whereDate('check_in_time', '>=', $date))
            ->when($filters['sampai'] ?? null, fn ($q, $date) => $q->whereDate('check_in_time', '<=', $date))
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where(fn ($q) => $q->where('name', 'ilike', $like)->orWhere('phone', 'ilike', $like)
                    ->orWhere('institution', 'ilike', $like)->orWhere('purpose', 'ilike', $like));
            })
            ->when(($filters['status'] ?? null) === 'di_lokasi', fn ($q) => $q->whereNull('check_out_time'))
            ->when(($filters['status'] ?? null) === 'keluar', fn ($q) => $q->whereNotNull('check_out_time'))
            ->when($filters['kategori'] ?? null, fn ($q, $category) => $q->where('institution_category', $category))
            ->latest('check_in_time')
            ->paginate($filters['per_halaman'] ?? 25)
            ->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (Visitor $visitor) => self::present($visitor)),
            'halaman' => $page->currentPage(),
            'total' => $page->total(),
            'halaman_terakhir' => $page->lastPage(),
        ]);
    }

    /** POST /api/buku-tamu: register a guest at the counter. */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Visitor::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'institution' => ['nullable', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:500'],
            'person_to_meet_id' => ['required', Rule::exists(User::class, 'id')->where('is_active', true)],
        ]);

        $visitor = $this->frontDesk->registerGuest($data, $request->user());

        return response()->json(self::present($visitor), 201);
    }

    /** POST /api/buku-tamu/{visitor}/keluar: check a guest out. */
    public function checkOut(Visitor $visitor): JsonResponse
    {
        $this->authorize('checkOut', $visitor);

        try {
            $this->frontDesk->checkOut($visitor);
        } catch (TicketActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(self::present($visitor->fresh()));
    }

    /** GET /api/buku-tamu/{visitor}: one visit, its guest card and the guest's earlier visits (same phone). */
    public function show(Visitor $visitor): JsonResponse
    {
        $this->authorize('view', $visitor);

        $history = filled($visitor->phone)
            ? Visitor::where('phone', $visitor->phone)->whereKeyNot($visitor->id)->latest('check_in_time')->limit(20)->get()
            : collect();

        return response()->json(self::present($visitor) + [
            'email' => $visitor->email,
            'catatan' => $visitor->notes,
            'riwayat_kunjungan' => $history->map(fn (Visitor $earlier) => [
                'id' => $earlier->id,
                'masuk' => $earlier->check_in_time?->toIso8601String(),
                'keluar' => $earlier->check_out_time?->toIso8601String(),
                'keperluan' => $earlier->purpose,
                'bertemu' => $earlier->person_to_meet,
            ])->values(),
        ]);
    }

    /** PATCH /api/buku-tamu/{visitor}/catatan: a note about the visit. */
    public function note(Request $request, Visitor $visitor): JsonResponse
    {
        $this->authorize('note', $visitor);

        $visitor->update($request->validate(['notes' => ['nullable', 'string', 'max:1000']]));

        return response()->json(self::present($visitor) + ['catatan' => $visitor->notes]);
    }

    public static function present(Visitor $visitor): array
    {
        return [
            'id' => $visitor->id,
            'nama' => $visitor->name,
            'telepon' => $visitor->phone,
            'instansi' => $visitor->institution,
            'keperluan' => $visitor->purpose,
            'bertemu' => $visitor->person_to_meet,
            'masuk' => $visitor->check_in_time?->toIso8601String(),
            'keluar' => $visitor->check_out_time?->toIso8601String(),
            'kartu_tamu' => route('visitors.print', $visitor),
        ];
    }
}
