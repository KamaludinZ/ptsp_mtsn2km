<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\TicketActionException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorMaster;
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

    /** GET /api/buku-tamu?tanggal=&dari=&sampai=&q=&status=di_lokasi|keluar&jenis=kunjungan|pemohon&kategori=&per_halaman= */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Visitor::class);

        $filters = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['di_lokasi', 'keluar'])],
            'jenis' => ['nullable', Rule::in(array_keys(Visitor::GUEST_TYPES))],
            'kategori' => ['nullable', 'string', 'max:255'],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $page = Visitor::query()
            ->with('service:id,name')
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
            ->when($filters['jenis'] ?? null, fn ($q, $type) => $q->where('guest_type', $type))
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

    /**
     * POST /api/buku-tamu: register a guest at the counter, as the Buku Tamu
     * form does. jenis=kunjungan (default): name, purpose (free text, or
     * Lainnya + purpose_other), person_to_meet_id?; jenis=pemohon: the
     * visitor book's applicant fields (FrontDeskService::serviceApplicantRules).
     * obscure_name masks the name on the public list.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Visitor::class);

        $type = $request->validate(['jenis' => ['nullable', Rule::in(array_keys(Visitor::GUEST_TYPES))]])['jenis'] ?? 'kunjungan';

        if ($type === 'pemohon') {
            $data = $request->validate(FrontDeskService::serviceApplicantRules(), FrontDeskService::GUEST_MESSAGES, FrontDeskService::GUEST_ATTRIBUTES);

            return response()->json(self::present($this->frontDesk->register('pemohon', $data, $request->user())), 201);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'institution' => ['nullable', 'string', 'max:255'],
            'institution_category' => ['nullable', Rule::in(Visitor::institutionCategoryKeys())],
            'purpose' => ['required', 'string', 'max:500'],
            'purpose_other' => ['required_if:purpose,' . VisitorMaster::OTHER, 'nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'person_to_meet_id' => ['nullable', Rule::exists(User::class, 'id')->where('is_active', true)],
            'obscure_name' => ['nullable', 'boolean'],
        ], FrontDeskService::GUEST_MESSAGES, FrontDeskService::GUEST_ATTRIBUTES);

        return response()->json(self::present($this->frontDesk->register('kunjungan', $data, $request->user())), 201);
    }

    /** POST /api/buku-tamu/{visitor}/keluar (waktu?: when the guest left, for a visit closed afterwards): check a guest out. */
    public function checkOut(Request $request, Visitor $visitor): JsonResponse
    {
        $this->authorize('checkOut', $visitor);
        $leftAt = $request->validate(['waktu' => ['nullable', 'date']])['waktu'] ?? null;

        try {
            $this->frontDesk->checkOut($visitor, $request->user(), null, $leftAt ? \Illuminate\Support\Carbon::parse($leftAt) : null);
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

        $checkout = $visitor->checkouts()->with('officer:id,name')->first();

        return response()->json(self::present($visitor) + [
            'email' => $visitor->email,
            'catatan' => $visitor->notes,
            'checkout' => $checkout ? [
                'cara' => $checkout->method,
                'cara_label' => \App\Models\VisitorCheckout::METHODS[$checkout->method],
                'oleh' => $checkout->officer?->name,
                'pada' => $checkout->checked_out_at->toIso8601String(),
            ] : null,
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
            'jenis' => $visitor->guest_type,
            'jenis_label' => Visitor::GUEST_TYPES[$visitor->guest_type] ?? null,
            'status_pemohon' => $visitor->applicant_type,
            'layanan' => $visitor->service?->name,
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
