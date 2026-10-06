<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use App\Support\ContentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Pengumuman for administrators (Manajemen Konten): every announcement,
 * whatever its status, with filters; the public list is /api/publik/pengumuman.
 */
class PengumumanController extends Controller
{
    private const SORTS = ['terbaru' => ['publish_date', 'desc'], 'terlama' => ['publish_date', 'asc'], 'judul' => ['title', 'asc'], 'dilihat' => ['view_count', 'desc']];

    private const ATTACHMENTS = 'pengumuman-attachments';

    /** GET /api/pengumuman?status=&kategori=&q=&dari=&sampai=&urut=&per_halaman= */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Pengumuman::class);

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(ContentStatus::LABELS))],
            'kategori' => ['nullable', 'string', 'max:50'],
            'q' => ['nullable', 'string', 'max:100'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'urut' => ['nullable', Rule::in(array_keys(self::SORTS))],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], ['sampai.after_or_equal' => 'Tanggal "sampai" tidak boleh sebelum tanggal "dari".']);

        [$column, $direction] = self::SORTS[$filters['urut'] ?? 'terbaru'];

        $page = Pengumuman::query()->with('user:id,name')
            ->when($filters['status'] ?? null, fn ($q, string $status) => $q->withStatus($status))
            ->when($filters['kategori'] ?? null, fn ($q, string $category) => $q->where('category', Str::lower($category)))
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where(fn ($q) => $q->where('title', 'ilike', $like)->orWhere('content', 'ilike', $like)->orWhere('author', 'ilike', $like));
            })
            ->when($filters['dari'] ?? null, fn ($q, $date) => $q->whereDate('publish_date', '>=', $date))
            ->when($filters['sampai'] ?? null, fn ($q, $date) => $q->whereDate('publish_date', '<=', $date))
            ->orderBy($column, $direction)->orderByDesc('id')
            ->paginate($filters['per_halaman'] ?? 25)
            ->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (Pengumuman $item) => $this->present($item)),
            'halaman' => $page->currentPage(),
            'total' => $page->total(),
            'halaman_terakhir' => $page->lastPage(),
            'jumlah_per_status' => ContentStatus::counts(),
            'kategori' => Pengumuman::categories(),
        ]);
    }

    /** GET /api/pengumuman/{pengumuman} */
    public function show(Pengumuman $pengumuman): JsonResponse
    {
        $this->authorize('viewAny', Pengumuman::class);

        return response()->json($this->present($pengumuman->load('user:id,name'), full: true));
    }

    /** POST /api/pengumuman (multipart when a lampiran is sent) */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Pengumuman::class);

        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $data['author'] ??= $request->user()->name;
        $data['is_active'] ??= true; // as in the form: published unless sent as a draft

        $item = Pengumuman::create($data);

        return response()->json($this->present($item->load('user:id,name'), full: true), 201);
    }

    /** PATCH /api/pengumuman/{pengumuman}: only the fields sent change. */
    public function update(Request $request, Pengumuman $pengumuman): JsonResponse
    {
        $this->authorize('update', $pengumuman);

        $data = $this->validated($request, $pengumuman);
        $old = $pengumuman->attachment;

        $pengumuman->update($data);

        if ($old && array_key_exists('attachment', $data) && $data['attachment'] !== $old) {
            Storage::disk('public')->delete($old);
        }

        return response()->json($this->present($pengumuman->load('user:id,name'), full: true));
    }

    /** DELETE /api/pengumuman/{pengumuman}: the attachment goes with it. */
    public function destroy(Pengumuman $pengumuman): JsonResponse
    {
        $this->authorize('delete', $pengumuman);

        $pengumuman->delete();

        return response()->json(null, 204);
    }

    /** POST /api/pengumuman/{pengumuman}/{aksi}: tayangkan, draf or akhiri. */
    public function transition(Pengumuman $pengumuman, string $action): JsonResponse
    {
        $this->authorize('publish', $pengumuman);

        match ($action) {
            'tayangkan' => ContentStatus::publish($pengumuman),
            'draf' => $pengumuman->update(['is_active' => false]),
            'akhiri' => ContentStatus::of($pengumuman) === 'tayang'
                ? ContentStatus::end($pengumuman)
                : abort(422, 'Hanya pengumuman yang sedang tayang yang dapat diakhiri.'),
        };

        $item = $pengumuman->fresh()->load('user:id,name');

        return response()->json([
            'message' => 'Status pengumuman: ' . ContentStatus::LABELS[$item->status()] . '.',
            'data' => $this->present($item, full: true),
        ]);
    }

    private function validated(Request $request, ?Pengumuman $item = null): array
    {
        $required = $item ? 'sometimes' : 'required';
        $publish = $request->input('tanggal_tayang', $item?->publish_date?->toDateString());
        // Moving only the publish date must not pass the end date already saved
        $keptEnd = $item && ! $request->has('tanggal_berakhir') ? $item->end_date?->toDateString() : null;

        $data = $request->validate([
            'judul' => [$required, 'string', 'max:255'],
            'isi' => [$required, 'string', 'max:65000'],
            'kategori' => ['sometimes', 'nullable', 'string', 'max:50'],
            'penulis' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tanggal_tayang' => [$required, 'date', ...($keptEnd ? ['before_or_equal:' . $keptEnd] : [])],
            'tanggal_berakhir' => ['sometimes', 'nullable', 'date', ...($publish ? ['after_or_equal:' . $publish] : [])],
            'aktif' => ['sometimes', 'boolean'],
            'tautan' => ['sometimes', 'nullable', 'url:http,https', 'max:255'],
            'lampiran' => ['sometimes', 'nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
            'hapus_lampiran' => ['sometimes', 'boolean'],
        ], [
            'tanggal_berakhir.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal tayang.',
            'tanggal_tayang.before_or_equal' => 'Tanggal tayang tidak boleh setelah tanggal berakhir.',
            'lampiran.mimes' => 'Lampiran harus PDF, Word, atau gambar (JPG/PNG).',
            'lampiran.max' => 'Lampiran maksimal 10 MB.',
        ]);

        $map = ['judul' => 'title', 'penulis' => 'author', 'tanggal_tayang' => 'publish_date', 'tanggal_berakhir' => 'end_date', 'aktif' => 'is_active', 'tautan' => 'url'];
        $values = [];
        foreach ($map as $input => $column) {
            if (array_key_exists($input, $data)) {
                $values[$column] = $data[$input];
            }
        }

        if (array_key_exists('isi', $data)) {
            $values['content'] = (string) str($data['isi'])->sanitizeHtml();
        }
        if (array_key_exists('kategori', $data)) {
            $values['category'] = filled($data['kategori']) ? Str::lower(trim($data['kategori'])) : null;
        }
        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $values['attachment'] = $file->storeAs(self::ATTACHMENTS, Str::uuid() . '.' . $file->guessExtension(), 'public');
        } elseif ($request->boolean('hapus_lampiran')) {
            $values['attachment'] = null;
        }

        return $values;
    }

    private function present(Pengumuman $item, bool $full = false): array
    {
        $status = $item->status();

        return [
            'id' => $item->id,
            'judul' => $item->title,
            'kategori' => $item->category,
            'status' => $status,
            'status_label' => ContentStatus::LABELS[$status],
            'aktif' => $item->is_active,
            'tanggal_tayang' => $item->publish_date?->toDateString(),
            'tanggal_berakhir' => $item->end_date?->toDateString(),
            'penulis' => $item->authorName(),
            'dilihat' => (int) $item->view_count,
            'lampiran' => $item->attachment ? asset('storage/' . $item->attachment) : null,
            'tautan' => $item->url,
            'halaman_publik' => $status === 'tayang' ? route('pengumuman.show', $item) : null,
            'diperbarui' => $item->updated_at?->toIso8601String(),
        ] + ($full ? ['isi' => $item->content] : ['ringkasan' => Str::limit(strip_tags((string) $item->content), 200)]);
    }
}
