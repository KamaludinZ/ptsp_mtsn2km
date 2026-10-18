<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\TicketActionException;
use App\Filament\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use App\Services\FrontDeskService;
use App\Support\Applicants;
use App\Support\RoleAccess;
use App\Support\TicketDocuments;
use App\Support\TicketLabels;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Pendaftaran walk-in di loket as JSON: the front desk registers a request for a visiting applicant. */
class WalkInController extends Controller
{
    /**
     * GET /api/loket/tipe-pemohon — aturan pemohon resmi untuk penyaring loket:
     * tujuh peran pemohon (kode yang dipakai `tipe`/`kategori`) dan peran
     * staf/pimpinan yang bukan pemohon.
     */
    public function applicantTypes(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('frontdesk.access'), 403);

        $row = fn (string $role) => ['kode' => $role, 'label' => RoleAccess::roleLabel($role)];

        return response()->json([
            'data' => array_map($row, Applicants::ROLES),
            'bukan_pemohon' => array_map($row, Applicants::NON_APPLICANT_ROLES),
        ]);
    }

    /**
     * GET /api/loket/pemohon?q=&tipe=&per_halaman= — akun pemohon terdaftar
     * (aturan pemohon resmi, hanya yang aktif) untuk dipilih petugas loket,
     * dicari lewat nama, email, atau nomor WhatsApp.
     */
    public function applicants(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('frontdesk.access'), 403);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'tipe' => ['nullable', Rule::in(Applicants::ROLES)],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:50'],
        ], [
            'tipe.in' => 'Tipe pengguna harus salah satu peran pemohon: ' . implode(', ', Applicants::ROLES) . '.',
        ]);

        $page = Applicants::lookup($filters['q'] ?? null, $filters['tipe'] ?? null)
            ->with('roles:id,name')
            ->withCount('tickets')
            ->paginate($filters['per_halaman'] ?? 20)
            ->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (User $user) => self::applicantItem($user)),
            'meta' => ['halaman' => $page->currentPage(), 'per_halaman' => $page->perPage(), 'total' => $page->total(), 'halaman_terakhir' => $page->lastPage()],
        ]);
    }

    /**
     * GET /api/loket/pemohon/{user} — rincian akun pemohon terpilih (hanya
     * baca) agar petugas memastikan pemohonnya: data akun, permohonan
     * terakhir, dan tautan perbaikan data bagi yang boleh membukanya.
     */
    public function applicant(Request $request, int $user): JsonResponse
    {
        abort_unless($request->user()->can('frontdesk.access'), 403);

        // Hanya pemohon aktif (aturan pemohon resmi); akun lain tidak dibuka lewat loket.
        $applicant = Applicants::lookup()->with('roles:id,name')->withCount('tickets')->find($user);
        abort_if($applicant === null, 404, 'Akun pemohon tidak ditemukan atau tidak aktif.');

        return response()->json(self::applicantItem($applicant) + [
            'terdaftar_sejak' => $applicant->created_at?->toDateString(),
            'permohonan_terakhir' => $applicant->tickets()->with('service:id,name')->latest('id')->limit(5)->get()
                ->map(fn ($ticket) => [
                    'nomor_tiket' => $ticket->ticket_number,
                    'layanan' => $ticket->service?->name,
                    'status' => $ticket->status,
                    'status_label' => TicketLabels::status($ticket->status),
                    'diajukan' => $ticket->created_at?->toDateString(),
                ]),
            // Data pemohon hanya diperbaiki di Pengguna › Pemohon (administrator).
            'tautan_perbaikan' => $request->user()->hasRole('admin')
                ? UserResource::getUrl('index', ['activeTab' => 'pemohon', 'tableSearch' => $applicant->email], panel: 'admin')
                : null,
        ]);
    }

    /** Akun pemohon dalam daftar/rincian loket (relasi roles dan tickets_count sudah dimuat). */
    private static function applicantItem(User $user): array
    {
        $types = Applicants::rolesOf($user);

        return [
            'id' => $user->id,
            'nama' => $user->name,
            // Email buatan loket (…@walkin.local) bukan email pemohon.
            'email' => str_ends_with((string) $user->email, '@walkin.local') ? null : $user->email,
            'whatsapp' => $user->whatsapp_number,
            'tipe' => $types,
            'tipe_label' => collect($types)->map(fn (string $role) => RoleAccess::roleLabel($role))->join(', '),
            'jumlah_permohonan' => $user->tickets_count,
        ];
    }

    /** POST /api/loket/permohonan (multipart when berkas[] is sent); pemohon_id for an already registered applicant */
    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('frontdesk.access'), 403);

        $data = $request->validate([
            // Seperti form loket: "terdaftar" wajib memilih akun, "baru" tidak memakai akun terpilih.
            'status_pemohon' => ['nullable', 'in:baru,terdaftar'],
            // Pemohon terdaftar (GET /api/loket/pemohon): permohonan masuk ke akun itu,
            // tanpa nama/WhatsApp/email dan tanpa membuat akun baru.
            'pemohon_id' => ['required_if:status_pemohon,terdaftar', 'prohibited_if:status_pemohon,baru', 'nullable', 'integer', function (string $attribute, $value, \Closure $fail) {
                if (! Applicants::lookup()->whereKey($value)->exists()) {
                    $fail('Akun pemohon tidak ditemukan, tidak aktif, atau bukan pemohon.');
                }
            }],
            'nama' => ['required_without:pemohon_id', 'nullable', 'string', 'max:255'],
            'kategori' => ['required_without:pemohon_id', 'nullable', Rule::in(Applicants::ROLES)],
            'whatsapp' => ['required_without:pemohon_id', 'nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'layanan' => ['required', 'string'],
            'keterangan' => ['required', 'string', 'max:1000'],
            'prioritas' => ['nullable', 'in:normal,high,urgent'],
            'berkas' => ['nullable', 'array', 'max:10'],
            'berkas.*' => ['file', 'max:10240', 'mimes:' . TicketDocuments::MIMES],
        ], [
            'pemohon_id.required_if' => 'Pilih akun pemohon terdaftar (pemohon_id) dulu, atau kirim status_pemohon "baru".',
            'pemohon_id.prohibited_if' => 'Pemohon baru tidak memakai pemohon_id; kirim status_pemohon "terdaftar" untuk akun terpilih.',
        ]);

        $applicant = filled($data['pemohon_id'] ?? null) ? User::find($data['pemohon_id']) : null;

        if ($applicant) {
            // Kategori mengikuti peran pemohon akun; bila dikirim, harus salah satunya.
            $types = Applicants::rolesOf($applicant);
            if (filled($data['kategori'] ?? null) && ! in_array($data['kategori'], $types, true)) {
                return response()->json(['message' => 'Kategori tidak sesuai dengan peran pemohon akun ini.', 'errors' => ['kategori' => ['Pilih salah satu: ' . implode(', ', $types) . '.']]], 422);
            }
            $data['kategori'] = $data['kategori'] ?? $types[0];
        }

        // Disaring sesuai kategori pemohon, sebelum berkas disimpan.
        $service = Service::query()->where('slug', $data['layanan'])->requestable($data['kategori'], 'offline')->availableFor($data['kategori'])->first();

        if (! $service) {
            return response()->json(['message' => 'Layanan tidak tersedia di loket untuk kategori pemohon ini.', 'errors' => ['layanan' => ['Layanan tidak tersedia.']]], 422);
        }

        $files = collect($request->file('berkas', []))
            ->mapWithKeys(fn ($upload) => [TicketDocuments::store($upload, 'ticket-files') => $upload->getClientOriginalName()])
            ->all();

        try {
            [$ticket, $link] = app(FrontDeskService::class)->registerWalkInLinked([
                'service_id' => $service->id,
                'applicant_name' => $data['nama'] ?? null,
                'applicant_type' => $data['kategori'],
                'applicant_phone' => $data['whatsapp'] ?? null,
                'applicant_email' => $data['email'] ?? null,
                'description' => $data['keterangan'],
                'priority' => $data['prioritas'] ?? 'normal',
            ], $files, $request->user(), $applicant);
        } catch (TicketActionException $e) {
            // Permohonan tidak jadi dibuat: berkas yang sudah tersimpan ikut dibuang.
            Storage::disk('local')->delete(array_keys($files));

            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'nomor_tiket' => $ticket->ticket_number,
            'status' => $ticket->status,
            'pemohon' => $ticket->user?->name,
            'pemohon_id' => $ticket->user_id,
            // Hasil penautan akun: terpilih | tertaut (dicocokkan lewat email/whatsapp) | baru.
            'akun_pemohon' => $link->toArray() + ['keterangan' => $link->message()],
            'target_selesai' => $ticket->estimated_completion_date?->toDateString(),
            'tanda_terima' => route('tickets.receipt', $ticket),
        ], 201);
    }
}
