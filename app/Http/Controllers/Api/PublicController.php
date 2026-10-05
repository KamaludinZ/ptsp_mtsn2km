<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Complaint;
use App\Models\ComplaintStatusLog;
use App\Models\Faq;
use App\Models\Pengumuman;
use App\Models\Service;
use App\Models\VisitorMaster;
use App\Services\ComplaintService;
use App\Services\FrontDeskService;
use App\Models\ServiceTemplate;
use App\Support\ServiceDisposition;
use App\Support\TicketTracking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Public (no sign-in) data for the madrasah's own site, kiosk or apps. */
class PublicController extends Controller
{
    /** GET /api/publik/profil: identity, contact details and opening hours. */
    public function profile(): JsonResponse
    {
        $setting = fn (string $key, $default = null) => AppSetting::get($key, $default) ?: $default;

        return response()->json([
            'nama' => app_brand_name(),
            'nama_lengkap' => $setting('app_name_full', app_brand_name()),
            'tagline' => $setting('app_tagline'),
            'logo' => ($logo = $setting('app_logo')) ? asset($logo) : null,
            'kontak' => [
                'alamat' => $setting('contact_address'),
                'telepon' => $setting('contact_phone'),
                'email' => $setting('contact_email'),
                'situs' => $setting('contact_website'),
                'whatsapp_ptsp' => $setting('contact_whatsapp_ptsp'),
                'whatsapp_pengaduan' => $setting('contact_whatsapp_pengaduan'),
            ],
            'jam_layanan' => [
                'senin_kamis' => $setting('operating_hours_weekday'),
                'jumat' => $setting('operating_hours_friday'),
                'sabtu_minggu' => $setting('operating_hours_weekend'),
            ],
            'media_sosial' => array_filter([
                'facebook' => $setting('social_facebook'),
                'twitter' => $setting('social_twitter'),
                'instagram' => $setting('social_instagram'),
                'youtube' => $setting('social_youtube'),
            ]),
        ])->setPublic()->setMaxAge(300);
    }

    /** GET /api/publik/layanan?kategori={id}&q=: active services open to the public. */
    public function services(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'kategori' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $services = Service::query()
            ->where('is_active', true)
            ->availableFor('umum')
            ->with('categories:id,name')
            ->when($filters['kategori'] ?? null, fn ($q, $id) => $q->whereHas('categories', fn ($c) => $c->whereKey($id)))
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('name', 'ilike', '%' . addcslashes($term, '%_\\') . '%'))
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $services->map(fn (Service $service) => [
                'nama' => $service->name,
                'slug' => $service->slug,
                'ringkasan' => Str::limit(strip_tags((string) $service->description), 200),
                'kategori' => $service->categories->pluck('name')->values(),
                'waktu_penyelesaian' => $service->processing_time,
                'biaya' => (float) $service->fee,
                'mode' => $service->mode,
                'detail' => route('api.publik.layanan.detail', $service->slug),
            ])->values(),
        ])->setPublic()->setMaxAge(300);
    }

    /**
     * GET /api/publik/layanan/{slug}/template: the service's active template
     * berkas, like the download links on the service page (any active
     * service: templates are public).
     */
    public function serviceTemplates(string $slug): JsonResponse
    {
        $service = Service::where('slug', $slug)->where('is_active', true)->with('activeTemplates')->firstOrFail();
        $templates = $service->activeTemplates;

        return response()->json([
            'layanan' => ['nama' => $service->name, 'slug' => $service->slug],
            'data' => $templates->map(fn (ServiceTemplate $template) => [
                'nama' => $template->nama,
                'wajib' => (bool) $template->is_required,
                'versi' => $template->versi,
                'jenis' => strtolower(pathinfo($template->file_name ?: $template->file_path, PATHINFO_EXTENSION)) ?: null,
                'ukuran' => $template->file_size,
                'tersedia' => $available = $template->isAvailable(),
                'unduh' => $available ? $template->downloadUrl() : null,
            ])->values(),
            'jumlah_wajib' => $templates->where('is_required', true)->count(),
            'unduh_semua' => $templates->filter->isAvailable()->count() > 1 ? route('onlineportal.service.templates.zip', $service->slug) : null,
            'pesan' => $templates->isEmpty() ? 'Layanan ini tidak memakai template berkas; cukup siapkan berkas persyaratan.' : null,
        ])->setPublic()->setMaxAge(300);
    }

    /** GET /api/publik/layanan/{slug}: the service standard, templates and how to apply. */
    public function service(string $slug): JsonResponse
    {
        $service = Service::where('slug', $slug)
            ->where('is_active', true)
            ->availableFor('umum')
            ->with(['categories:id,name', 'activeTemplates'])
            ->firstOrFail();

        return response()->json([
            'nama' => $service->name,
            'slug' => $service->slug,
            'kode' => $service->code,
            'deskripsi' => $service->description,
            'kategori' => $service->categories->pluck('name')->values(),
            'persyaratan' => $service->getAttribute('requirements'),
            'mekanisme' => $service->mechanism,
            'waktu_penyelesaian' => $service->processing_time,
            'biaya' => (float) $service->fee,
            'produk' => $service->product,
            'penanganan_pengaduan' => $service->complaint_handling,
            'mode' => $service->mode,
            'disposisi' => ServiceDisposition::mode($service->disposition_mode),
            'template_berkas' => $service->activeTemplates->map(fn (ServiceTemplate $template) => [
                'nama' => $template->nama,
                'versi' => $template->versi,
                'wajib' => $template->is_required,
                'tersedia' => $available = $template->isAvailable(),
                'unduh' => $available ? $template->downloadUrl() : null,
            ])->values(),
            'perlu_template' => $service->activeTemplates->isNotEmpty(),
            'ajukan' => route('onlineportal.service.apply', $service->slug),
        ])->setPublic()->setMaxAge(300);
    }

    /** GET /api/publik/pengumuman?q=&kategori=&per_halaman=: published announcements, newest first. */
    public function announcements(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', 'max:50'],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $page = Pengumuman::active()->with('user:id,name')
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where(fn ($q) => $q->where('title', 'ilike', $like)->orWhere('content', 'ilike', $like));
            })
            ->when($filters['kategori'] ?? null, fn ($q, $category) => $q->where('category', Str::lower(trim($category))))
            ->orderByDesc('publish_date')->orderByDesc('id')
            ->paginate($filters['per_halaman'] ?? 10)
            ->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (Pengumuman $item) => [
                'id' => $item->id,
                'judul' => $item->title,
                'kategori' => $item->category,
                'kategori_label' => $item->category ? Str::headline($item->category) : null,
                'tanggal' => $item->publish_date?->toDateString(),
                'berakhir' => $item->end_date?->toDateString(),
                'penulis' => $item->authorName(),
                'ringkasan' => Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) str($item->content)->sanitizeHtml())))), 200),
                'ada_lampiran' => filled($item->attachment),
                'detail' => route('api.publik.pengumuman.detail', $item),
                'halaman' => route('pengumuman.show', $item),
            ]),
            'halaman' => $page->currentPage(),
            'total' => $page->total(),
            'halaman_terakhir' => $page->lastPage(),
            // Categories in use on the site now, for the filter chips
            'kategori' => collect(Pengumuman::categories(onlyPublished: true))->map(fn (string $c) => ['nilai' => $c, 'label' => Str::headline($c)])->all(),
        ])->setPublic()->setMaxAge(120);
    }

    /** GET /api/publik/pengumuman/{pengumuman} */
    public function announcement(int $pengumuman): JsonResponse
    {
        $item = Pengumuman::active()->findOrFail($pengumuman);

        return response()->json([
            'id' => $item->id,
            'judul' => $item->title,
            'kategori' => $item->category,
            'kategori_label' => $item->category ? Str::headline($item->category) : null,
            'tanggal' => $item->publish_date?->toDateString(),
            'berakhir' => $item->end_date?->toDateString(),
            'penulis' => $item->authorName(),
            'dilihat' => (int) $item->view_count,
            'isi' => (string) str($item->content)->sanitizeHtml(),
            'lampiran' => $item->attachment ? asset('storage/' . $item->attachment) : null,
            'tautan' => $item->url,
            'halaman' => route('pengumuman.show', $item),
        ])->setPublic()->setMaxAge(120);
    }

    /**
     * GET /api/publik/faq?q=&kelompok=: active questions in display order,
     * flat (data) and grouped by kelompok as on the site; answers are sanitised HTML.
     */
    public function faq(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kelompok' => ['nullable', 'string', 'max:50'],
        ]);

        $faqs = Faq::published()
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where(fn ($q) => $q->where('question', 'ilike', $like)->orWhere('answer', 'ilike', $like));
            })
            ->when(isset($filters['kelompok']), fn ($q) => $q->inCategory($filters['kelompok'] === 'umum' ? null : Str::lower($filters['kelompok'])))
            ->get();

        $item = fn (Faq $faq) => [
            'id' => $faq->id,
            'kelompok' => $faq->categoryLabel(),
            'pertanyaan' => $faq->question,
            'jawaban' => $faq->safeAnswer(),
        ];

        return response()->json([
            'data' => $faqs->map($item)->values(),
            'total' => $faqs->count(),
            // Published order already runs group by group (Umum last)
            'kelompok' => $faqs->groupBy(fn (Faq $faq) => $faq->category ?? 'umum')
                ->map(fn ($group, string $key) => [
                    'nilai' => $key,
                    'label' => $group->first()->categoryLabel(),
                    'jumlah' => $group->count(),
                    'pertanyaan' => $group->map($item)->values(),
                ])->values(),
        ])->setPublic()->setMaxAge(300);
    }

    /** GET /api/publik/lacak/{nomor}?email=: progress of a request; details only for its applicant. */
    public function track(Request $request, string $number): JsonResponse
    {
        $email = $request->validate(['email' => ['nullable', 'email']])['email'] ?? null;
        $ticket = TicketTracking::find($number);

        if (! $ticket || ($email && ! TicketTracking::isOwner($ticket, $email))) {
            return response()->json(['message' => 'Nomor tiket tidak ditemukan'], 404);
        }

        return response()->json(TicketTracking::summary($ticket, TicketTracking::isOwner($ticket, $email)));
    }

    /** POST /api/publik/buku-tamu: a guest signs the guest book (e.g. from a kiosk). */
    public function signGuestBook(Request $request, FrontDeskService $frontDesk): JsonResponse
    {
        $data = $request->validate(FrontDeskService::selfRegistrationRules(), [
            'purpose_other.required_if' => 'Tuliskan tujuan kunjungan Anda.',
        ]);

        $visitor = $frontDesk->selfRegister($data);

        return response()->json([
            'pesan' => 'Selamat datang, ' . $visitor->name . '. Silakan menunggu di ruang tamu.',
            'masuk' => $visitor->check_in_time?->toIso8601String(),
            'ringkasan' => collect($visitor->confirmationSummary())->map(fn ($nilai, $label) => ['label' => $label, 'nilai' => $nilai])->values(),
        ], 201);
    }

    /** GET /api/publik/buku-tamu/pilihan: the destinations the guest book offers. */
    public function guestBookOptions(): JsonResponse
    {
        return response()->json([
            'tujuan' => VisitorMaster::options('tujuan'),
            'keperluan' => VisitorMaster::options('keperluan'),
        ])->setPublic()->setMaxAge(300);
    }

    /**
     * POST /api/publik/pengaduan (multipart when attaching): jenis=pengaduan|saran|whistleblowing
     * plus the fields of that form. Returns the report number for tracking.
     */
    public function submitReport(Request $request, ComplaintService $complaints): JsonResponse
    {
        $type = match ($request->validate(['jenis' => ['required', Rule::in(['pengaduan', 'saran', 'whistleblowing'])]])['jenis']) {
            'saran' => 'suggestion',
            'whistleblowing' => 'whistleblowing',
            default => 'complaint',
        };

        $data = $request->validate(ComplaintService::rulesFor($type));
        $complaint = $complaints->submit($type, $data, array_filter([$request->file('attachment')]));

        return response()->json([
            'nomor' => $type === 'suggestion' ? null : $complaint->complaint_number,
            'pesan' => match ($type) {
                'suggestion' => 'Terima kasih, saran Anda sudah kami terima.',
                'whistleblowing' => 'Laporan diterima dan dijaga kerahasiaannya. Simpan nomor laporan Anda.',
                default => 'Pengaduan diterima. Simpan nomor ini untuk melacak tindak lanjutnya.',
            },
        ], 201);
    }

    /** GET /api/publik/pengaduan/{nomor}?email=: a report's stages, for its reporter only. */
    public function trackReport(Request $request, string $number): JsonResponse
    {
        $email = $request->validate(['email' => ['required', 'email']])['email'];

        // Never look a report up by number alone (whistleblowing especially).
        $complaint = Complaint::where('complaint_number', $number)
            ->where(fn ($q) => $q->where('reporter_email', $email)->orWhere('complainant_email', $email))
            ->first();

        if (! $complaint) {
            return response()->json(['message' => 'Laporan tidak ditemukan.'], 404);
        }

        return response()->json([
            'nomor' => $complaint->complaint_number,
            'jenis' => Complaint::TYPES[$complaint->complaint_type] ?? $complaint->complaint_type,
            'judul' => $complaint->title,
            'status' => $complaint->status,
            'status_label' => Complaint::STATUSES[$complaint->status] ?? $complaint->status,
            'dikirim' => $complaint->created_at?->toIso8601String(),
            'tanggapan' => $complaint->response,
            'tahapan' => $complaint->statusLogs->map(fn (ComplaintStatusLog $log) => [
                'status' => $log->to_status,
                'status_label' => Complaint::STATUSES[$log->to_status] ?? $log->to_status,
                'waktu' => $log->created_at?->toIso8601String(),
                'tanggapan' => $log->response,
            ])->values(),
        ]);
    }
}
