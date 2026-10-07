<?php

namespace App\Http\Controllers\Api;

use App\Filament\Pages\System\SystemMonitor;
use App\Exceptions\TicketActionException;
use App\Http\Controllers\Controller;
use App\Models\AppUpdate;
use App\Models\SystemMetric;
use App\Models\User;
use App\Services\SystemMonitorService;
use App\Services\UpdateService;
use App\Support\LogReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Models\Activity;

/** Monitoring Sistem for administrators (same access as the panel page). */
class MonitoringController extends Controller
{
    public function __construct(private SystemMonitorService $monitor)
    {
    }

    /** GET /api/monitoring/aplikasi */
    public function application(): JsonResponse
    {
        $this->authorizeAdmin();
        $app = $this->monitor->application();

        return response()->json([
            'status' => $this->monitor->overall($app, $this->monitor->server()),
            'aplikasi' => $app,
            'riwayat' => SystemMetric::where('recorded_at', '>=', now()->subDay())->orderBy('recorded_at')
                ->get(['recorded_at', 'app_up', 'database_ok', 'database_latency_ms', 'queue_pending', 'queue_failed', 'scheduler_ok', 'status']),
        ]);
    }

    /** GET /api/monitoring/server */
    public function server(): JsonResponse
    {
        $this->authorizeAdmin();
        $server = $this->monitor->server();

        return response()->json([
            'ambang' => ['peringatan' => SystemMonitorService::WARNING_AT, 'kritis' => SystemMonitorService::CRITICAL_AT],
            'server' => $server + [
                'level' => [
                    'cpu' => SystemMonitorService::level($server['load_percent']),
                    'memori' => SystemMonitorService::level($server['memory']['used_percent']),
                    'disk' => SystemMonitorService::level($server['disk']['used_percent']),
                ],
            ],
            'riwayat' => SystemMetric::where('recorded_at', '>=', now()->subDay())->orderBy('recorded_at')
                ->get(['recorded_at', 'cpu_percent', 'memory_percent', 'disk_percent', 'status']),
        ]);
    }

    /**
     * GET /api/monitoring/metrik?rentang=24jam|7hari|30hari - collected
     * snapshots: every snapshot for 24 hours, hourly averages for 7 days,
     * daily averages for 30 days, plus availability over the range.
     */
    public function metrics(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $ranges = ['24jam' => [now()->subDay(), null], '7hari' => [now()->subDays(7), 'hour'], '30hari' => [now()->subDays(SystemMetric::KEEP_DAYS), 'day']];
        $range = $request->validate(['rentang' => ['nullable', Rule::in(array_keys($ranges))]])['rentang'] ?? '24jam';
        [$from, $bucket] = $ranges[$range];

        $query = SystemMetric::where('recorded_at', '>=', $from);
        $total = (clone $query)->count();
        $up = (clone $query)->where('app_up', true)->where('database_ok', true)->count();

        $points = $bucket === null
            ? (clone $query)->orderBy('recorded_at')->get(['recorded_at', 'cpu_percent', 'memory_percent', 'disk_percent', 'database_latency_ms', 'queue_pending', 'status'])
                ->map(fn (SystemMetric $m) => [
                    'waktu' => $m->recorded_at->toIso8601String(),
                    'cpu' => $m->cpu_percent, 'memori' => $m->memory_percent, 'disk' => $m->disk_percent,
                    'latensi_db_ms' => $m->database_latency_ms, 'antrean' => $m->queue_pending, 'status' => $m->status,
                ])
            : (clone $query)->toBase()
                ->selectRaw("date_trunc('{$bucket}', recorded_at) as waktu")
                ->selectRaw('round(avg(cpu_percent)::numeric, 1) as cpu, round(avg(memory_percent)::numeric, 1) as memori, round(avg(disk_percent)::numeric, 1) as disk')
                ->selectRaw('round(avg(database_latency_ms)::numeric, 1) as latensi_db_ms, max(queue_pending) as antrean')
                ->selectRaw("case when bool_or(status = 'danger') then 'danger' when bool_or(status = 'warning') then 'warning' else 'success' end as status")
                ->groupBy('waktu')->orderBy('waktu')->get()
                ->map(fn ($row) => [
                    'waktu' => \Illuminate\Support\Carbon::parse($row->waktu)->toIso8601String(),
                    'cpu' => $row->cpu === null ? null : (float) $row->cpu, 'memori' => $row->memori === null ? null : (float) $row->memori,
                    'disk' => $row->disk === null ? null : (float) $row->disk, 'latensi_db_ms' => $row->latensi_db_ms === null ? null : (float) $row->latensi_db_ms,
                    'antrean' => $row->antrean === null ? null : (int) $row->antrean, 'status' => $row->status,
                ]);

        return response()->json([
            'rentang' => $range,
            'interval_menit' => 5,
            'jumlah_snapshot' => $total,
            'ketersediaan_persen' => $total ? round($up / $total * 100, 2) : null,
            'terakhir' => SystemMetric::latest('recorded_at')->value('recorded_at')?->toIso8601String(),
            'titik' => $points->values(),
        ]);
    }

    /** GET /api/monitoring/integrasi: Email/WhatsApp switches and the last 24 hours of deliveries. */
    public function integrations(): JsonResponse
    {
        $this->authorizeAdmin();

        return response()->json(['data' => collect($this->monitor->integrations())->map(fn (array $channel, string $key) => [
            'kanal' => $key,
            'label' => $channel['label'],
            'aktif' => $channel['enabled'],
            'terkirim_24_jam' => $channel['sent'],
            'gagal_24_jam' => $channel['failed'],
            'gagal_terakhir' => $channel['last_failure'] ? [
                'waktu' => $channel['last_failure']['at']?->toIso8601String(),
                'galat' => $channel['last_failure']['error'],
            ] : null,
        ])->values()]);
    }

    /** GET /api/monitoring/ip-diblokir: active blocks. */
    public function blockedIps(\App\Support\SecurityMonitor $security): JsonResponse
    {
        $this->authorizeAdmin();

        return response()->json(['data' => collect($security->blockedIps())->map(fn (array $block, string $ip) => [
            'ip' => $ip,
            'alasan' => $block['reason'],
            'diblokir_oleh' => $block['blocked_by'],
            'diblokir' => $block['blocked_at'],
            'berakhir' => $block['expires_at'],
            'permanen' => $block['expires_at'] === null,
        ])->values()]);
    }

    /** POST /api/monitoring/ip-diblokir {ip, alasan, jam?}: block an address (jam empty = permanent). */
    public function blockIp(Request $request, \App\Support\SecurityMonitor $security): JsonResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'ip' => ['required', 'ip', Rule::notIn([$request->ip()])],
            'alasan' => ['required', 'string', 'max:255'],
            'jam' => ['nullable', 'integer', 'min:1', 'max:8760'],
        ], ['ip.not_in' => 'Alamat ini adalah IP Anda sendiri.']);

        $security->blockIp($data['ip'], $data['alasan'], $data['jam'] ?? null, $request->user()->email);
        activity('audit')->causedBy($request->user())
            ->withProperties(['ip' => $request->ip(), 'ip_diblokir' => $data['ip'], 'jam' => $data['jam'] ?? null])
            ->log("Memblokir IP {$data['ip']}");

        return response()->json(['message' => "{$data['ip']} diblokir.", 'blokir' => $security->blockedIps()[$data['ip']] ?? null], 201);
    }

    /** DELETE /api/monitoring/ip-diblokir/{ip}: lift a block. */
    public function unblockIp(Request $request, string $ip, \App\Support\SecurityMonitor $security): JsonResponse
    {
        $this->authorizeAdmin();

        if (! $security->unblockIp($ip, $request->user()->email)) {
            return response()->json(['message' => "{$ip} tidak ada di daftar blokir."], 404);
        }

        activity('audit')->causedBy($request->user())
            ->withProperties(['ip' => $request->ip(), 'ip_dibuka' => $ip])
            ->log("Membuka blokir IP {$ip}");

        return response()->json(['message' => "Blokir {$ip} dibuka."]);
    }

    /** GET /api/monitoring/keamanan */
    public function security(): JsonResponse
    {
        $this->authorizeAdmin();
        $security = $this->monitor->security();

        return response()->json([
            'login_gagal_hari_ini' => $security['metrics']['failed_logins_today'],
            'login_gagal_minggu_ini' => $security['metrics']['failed_logins_this_week'],
            'penguncian_hari_ini' => $security['lockouts_today'],
            'akun_nonaktif' => $security['deactivated_accounts'],
            'sesi_aktif' => $security['active_sessions'],
            'aktivitas_mencurigakan_hari_ini' => $security['metrics']['suspicious_activities_today'],
            'ip_diblokir' => $security['blocked'],
            'https' => $security['https'],
            'skor_keamanan' => $security['metrics']['security_score'],
            'pemindaian_terakhir' => $security['last_scan']['timestamp'] ?? null,
            'temuan' => $security['findings'],
            'login_gagal_terbaru' => $security['recent_failed'],
        ]);
    }

    /**
     * GET /api/monitoring/log?jenis=aplikasi|akses|audit&level=&q=&dari=&sampai=&pengguna={id}&data={Model}
     * aplikasi: storage/logs entries; akses: sign-ins and sign-outs; audit: data changes.
     */
    public function logs(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $filters = $request->validate([
            'jenis' => ['nullable', Rule::in(['aplikasi', 'akses', 'audit'])],
            'level' => ['nullable', Rule::in(LogReader::LEVELS)],
            'q' => ['nullable', 'string', 'max:100'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'batas' => ['nullable', 'integer', 'min:1', 'max:500'],
            'pengguna' => ['nullable', 'integer'],
            'data' => ['nullable', 'string', 'max:50', 'regex:/^[A-Z][A-Za-z]+$/'], // model name, e.g. Ticket, User
        ]);
        $limit = $filters['batas'] ?? 100;

        if (($filters['jenis'] ?? 'aplikasi') === 'aplikasi') {
            return response()->json([
                'berkas' => ($file = LogReader::latestFile()) ? basename($file) : null,
                'data' => LogReader::entries($filters['level'] ?? null, $filters['q'] ?? null, $limit),
            ]);
        }

        $activities = Activity::query()
            ->with('causer')
            ->when($filters['jenis'] === 'akses', fn ($q) => $q->where('log_name', 'access'), fn ($q) => $q->where('log_name', '!=', 'access'))
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where(fn ($q) => $q->where('description', 'ilike', $like)
                    ->orWhereHasMorph('causer', [User::class], fn ($c) => $c->where('name', 'ilike', $like)->orWhere('email', 'ilike', $like)));
            })
            ->when($filters['pengguna'] ?? null, fn ($q, $id) => $q->where('causer_type', User::class)->where('causer_id', $id))
            ->when($filters['data'] ?? null, fn ($q, $type) => $q->where('subject_type', 'App\\Models\\' . $type))
            ->when($filters['dari'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['sampai'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest()->limit($limit)->get();

        return response()->json([
            'data' => $activities->map(fn (Activity $activity) => [
                'waktu' => $activity->created_at?->toIso8601String(),
                'kegiatan' => $activity->description,
                'pengguna' => $activity->causer?->name,
                'ip' => $activity->properties['ip'] ?? null,
                'data' => $activity->subject_type ? class_basename($activity->subject_type) . ' #' . $activity->subject_id : null,
            ])->values(),
        ]);
    }

    /** GET /api/monitoring/pembaruan?segarkan=1: running version vs the latest GitHub release. */
    public function updates(Request $request, UpdateService $updates): JsonResponse
    {
        $this->authorizeAdmin();

        $current = $this->monitor->version();
        $release = $updates->latestRelease(fresh: $request->boolean('segarkan'));

        return response()->json([
            'versi_berjalan' => $current,
            'repositori' => $updates->repository(),
            'rilis_terbaru' => isset($release['error']) ? null : $release,
            'galat' => $release['error'] ?? null,
            'pembaruan_tersedia' => isset($release['tag']) ? $updates->isNewer($release['tag'], $current) : null,
            'riwayat' => $updates->history()->map->toHistory(),
        ]);
    }

    /** GET /api/monitoring/pembaruan/riwayat?status=&sumber=&dari=&sampai=&per_halaman= */
    public function updateHistory(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(AppUpdate::STATUSES))],
            'sumber' => ['nullable', Rule::in(array_keys(AppUpdate::SOURCES))],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $page = AppUpdate::with('performer:id,name')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['sumber'] ?? null, fn ($q, $source) => $q->where('source', $source))
            ->when($filters['dari'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['sampai'] ?? null, fn ($q, $until) => $q->whereDate('created_at', '<=', $until))
            ->latest()->latest('id')
            ->paginate($filters['per_halaman'] ?? 20);

        return response()->json([
            'data' => collect($page->items())->map->toHistory(),
            'meta' => ['halaman' => $page->currentPage(), 'per_halaman' => $page->perPage(), 'total' => $page->total(), 'halaman_terakhir' => $page->lastPage()],
        ]);
    }

    /** GET /api/monitoring/pembaruan/{update} */
    public function showUpdate(AppUpdate $update): JsonResponse
    {
        $this->authorizeAdmin();

        return response()->json($update->load('performer:id,name')->toHistory());
    }

    /** POST /api/monitoring/pembaruan/unggah (multipart: berkas, checksum?) */
    public function uploadUpdate(Request $request, UpdateService $updates): JsonResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'berkas' => ['required', 'file', 'mimes:zip', 'max:' . UpdateService::MAX_PACKAGE_KB],
            'checksum' => ['nullable', 'regex:/^[A-Fa-f0-9]{64}$/'],
        ]);

        try {
            $update = $updates->storePackage($data['berkas'], $request->user(), $data['checksum'] ?? null);
        } catch (TicketActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        activity('audit')->causedBy($request->user())->performedOn($update)->log('Mengunggah paket pembaruan ' . $update->package_name);

        return response()->json(['id' => $update->id, 'status' => $update->status, 'versi' => $update->to_version, 'catatan' => $update->notes], 201);
    }

    /** POST /api/monitoring/pembaruan/{update}/terapkan */
    public function applyUpdate(Request $request, AppUpdate $update, UpdateService $updates): JsonResponse
    {
        $this->authorizeAdmin();

        try {
            $update = $updates->apply($update, $request->user());
        } catch (TicketActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        activity('audit')->causedBy($request->user())->performedOn($update)->log('Menerapkan paket pembaruan: ' . $update->status);

        return response()->json(['id' => $update->id, 'status' => $update->status, 'catatan' => $update->notes], $update->status === 'applied' ? 200 : 422);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(SystemMonitor::canAccess(), 403);
    }
}
