<?php

namespace App\Services;

use App\Models\SystemMetric;
use App\Models\User;
use App\Support\SecurityMonitor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Monitoring Sistem: the application's own health (version, database,
 * queue, scheduler, storage, response time) and the server it runs on
 * (CPU load, memory, disk, uptime, container). Readings that the host does
 * not expose are returned as null rather than guessed.
 */
class SystemMonitorService
{
    public const HEARTBEAT_KEY = 'monitor:scheduler-heartbeat';

    /** Usage (percent) from which a reading is a warning, and from which it is critical. */
    public const WARNING_AT = 75;

    public const CRITICAL_AT = 90;

    public static function level(?float $percent): string
    {
        return match (true) {
            $percent === null => 'gray',
            $percent >= self::CRITICAL_AT => 'danger',
            $percent >= self::WARNING_AT => 'warning',
            default => 'success',
        };
    }

    /** Called every minute by the scheduler. */
    public static function beat(): void
    {
        Cache::forever(self::HEARTBEAT_KEY, now()->toIso8601String());
    }

    public function version(): string
    {
        if ($version = config('app.version')) {
            return $version;
        }

        // Release ZIPs (scripts/build-release.sh) carry their version in RELEASE.
        if (is_file($release = base_path('RELEASE')) && ($version = trim((string) file_get_contents($release))) !== '') {
            return $version;
        }

        return Cache::remember('monitor:version', 600, function () {
            $git = base_path('.git');
            if (! is_dir($git)) {
                return 'tidak diketahui';
            }
            // No shell (and no "2>/dev/null"), so it works the same on Windows and Linux.
            try {
                $result = \Illuminate\Support\Facades\Process::path(base_path())->timeout(10)
                    ->run(['git', 'describe', '--tags', '--always']);
            } catch (\Throwable) {
                return 'tidak diketahui';
            }

            return ($result->successful() ? trim($result->output()) : '') ?: 'tidak diketahui';
        });
    }

    /** @return array{ok: bool, latency_ms: ?float, driver: string, error: ?string} */
    public function database(): array
    {
        $started = microtime(true);
        try {
            DB::select('select 1');

            return ['ok' => true, 'latency_ms' => round((microtime(true) - $started) * 1000, 1), 'driver' => DB::getDriverName(), 'error' => null];
        } catch (Throwable $e) {
            return ['ok' => false, 'latency_ms' => null, 'driver' => (string) config('database.default'), 'error' => mb_strimwidth($e->getMessage(), 0, 160, '…')];
        }
    }

    /** @return array{connection: string, pending: ?int, failed: ?int} */
    public function queue(): array
    {
        $count = fn (string $table) => Schema::hasTable($table) ? DB::table($table)->count() : null;

        return [
            'connection' => (string) config('queue.default'),
            'pending' => config('queue.default') === 'database' ? $count('jobs') : null,
            'failed' => $count('failed_jobs'),
        ];
    }

    /** @return array{ok: bool, last_run: ?string} */
    public function scheduler(): array
    {
        $last = Cache::get(self::HEARTBEAT_KEY);

        return ['ok' => $last && now()->diffInMinutes($last, true) <= 5, 'last_run' => $last];
    }

    /** @return array{free: ?int, total: ?int, used_percent: ?float} */
    public function disk(?string $path = null): array
    {
        $path ??= storage_path();
        $free = @disk_free_space($path) ?: null;
        $total = @disk_total_space($path) ?: null;

        return [
            'free' => $free ? (int) $free : null,
            'total' => $total ? (int) $total : null,
            'used_percent' => $free && $total ? round(($total - $free) / $total * 100, 1) : null,
        ];
    }

    /** Storage the app writes to: private documents and logs. */
    public function storage(): array
    {
        return [
            'disk' => $this->disk(storage_path()),
            'writable' => is_writable(storage_path('app')) && is_writable(storage_path('logs')),
        ];
    }

    public function application(): array
    {
        $database = $this->database();
        $scheduler = $this->scheduler();

        return [
            'up' => $database['ok'] && ! app()->isDownForMaintenance(),
            'maintenance' => app()->isDownForMaintenance(),
            'version' => $this->version(),
            'environment' => app()->environment(),
            'debug' => (bool) config('app.debug'),
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'database' => $database,
            'queue' => $this->queue(),
            'scheduler' => $scheduler,
            'storage' => $this->storage(),
            'response_ms' => defined('LARAVEL_START') ? round((microtime(true) - LARAVEL_START) * 1000) : null,
        ];
    }

    /** Sessions used within the session lifetime (file or database driver). */
    public function activeSessions(): ?int
    {
        $since = now()->subMinutes((int) config('session.lifetime', 120));

        return match (config('session.driver')) {
            'database' => Schema::hasTable('sessions') ? DB::table('sessions')->where('last_activity', '>=', $since->timestamp)->count() : null,
            'file' => collect(@glob(config('session.files') . '/*') ?: [])->filter(fn ($f) => @filemtime($f) >= $since->timestamp)->count(),
            default => null,
        };
    }

    /**
     * Risky configuration worth an administrator's attention.
     *
     * @return array<int, array{level: string, title: string, detail: string}>
     */
    public function findings(?array $lastScan = null): array
    {
        $production = app()->isProduction();
        $findings = [];
        $add = function (string $level, string $title, string $detail) use (&$findings) {
            $findings[] = compact('level', 'title', 'detail');
        };

        if (config('app.debug')) {
            $add($production ? 'danger' : 'warning', 'Mode debug aktif', 'Pesan galat lengkap bisa terlihat pengunjung. Setel APP_DEBUG=false di produksi.');
        }
        if (! str_starts_with((string) config('app.url'), 'https://')) {
            $add($production ? 'danger' : 'warning', 'APP_URL belum HTTPS', 'Tautan di email/WhatsApp dan cookie sesi sebaiknya memakai HTTPS.');
        }
        if (! config('session.secure') && $production) {
            $add('warning', 'Cookie sesi tanpa flag Secure', 'Setel SESSION_SECURE_COOKIE=true agar cookie hanya dikirim lewat HTTPS.');
        }
        if (config('queue.default') === 'sync' && $production) {
            $add('info', 'Antrean berjalan sinkron', 'Notifikasi dikirim saat permintaan berlangsung; pertimbangkan antrean database agar halaman tidak menunggu gateway.');
        }
        if (! is_dir(public_path('storage'))) {
            $add('danger', 'Tautan public/storage belum ada', 'Gambar slider, gambar editor, foto tamu dan logo tidak tampil (HTTP 403). Jalankan php artisan storage:link.');
        }
        if (blank(config('tinymce.api_key'))) {
            $add('warning', 'Editor teks belum aktif', 'Isi TINYMCE_API_KEY agar editor Layanan, Pengumuman dan FAQ bisa dipakai.');
        }
        if (! $this->scheduler()['ok']) {
            $add('warning', 'Scheduler tidak terdeteksi', 'Tugas berkala (arsip survei triwulan, detak monitoring) tidak berjalan.');
        }
        foreach ($lastScan['checks'] ?? [] as $name => $check) {
            if (in_array($check['status'] ?? null, ['fail', 'warning'], true)) {
                $add($check['status'] === 'fail' ? 'danger' : 'warning', 'Pemindaian: ' . $name, (string) ($check['message'] ?? ''));
            }
        }

        return $findings;
    }

    /**
     * Overall health from the application and server readings.
     *
     * @return array{level: string, label: string, issues: array<int, string>, checked_at: string}
     */
    public function overall(?array $app = null, ?array $server = null): array
    {
        $app ??= $this->application();
        $server ??= $this->server();
        $critical = [];
        $warnings = [];

        if (! $app['database']['ok']) {
            $critical[] = 'Database tidak terhubung';
        }
        if ($app['maintenance']) {
            $warnings[] = 'Mode perawatan aktif';
        }
        if (! $app['storage']['writable']) {
            $critical[] = 'Penyimpanan tidak dapat ditulis';
        }
        if (! $app['scheduler']['ok']) {
            $warnings[] = 'Scheduler tidak terdeteksi';
        }
        foreach (['CPU' => $server['load_percent'], 'Memori' => $server['memory']['used_percent'], 'Disk' => $server['disk']['used_percent']] as $name => $percent) {
            match (self::level($percent)) {
                'danger' => $critical[] = "{$name} {$percent}%",
                'warning' => $warnings[] = "{$name} {$percent}%",
                default => null,
            };
        }

        [$level, $label] = match (true) {
            (bool) $critical => ['danger', 'Kritis'],
            (bool) $warnings => ['warning', 'Perlu perhatian'],
            default => ['success', 'Sehat'],
        };

        return ['level' => $level, 'label' => $label, 'issues' => [...$critical, ...$warnings], 'checked_at' => now()->toIso8601String()];
    }

    /** Keamanan aplikasi: sign-in failures, lockouts, sessions, blocks, HTTPS and risky settings. */
    public function security(): array
    {
        $security = app(SecurityMonitor::class);
        $lastScan = $security->lastScan();

        return [
            'metrics' => $security->metrics(),
            'lockouts_today' => $security->lockoutsToday(),
            'deactivated_accounts' => User::where('is_active', false)->count(),
            'active_sessions' => $this->activeSessions(),
            'https' => request()->isSecure() || str_starts_with((string) config('app.url'), 'https://'),
            'recent_failed' => $security->recentFailedLogins(),
            'blocked' => $security->blockedIps(),
            'last_scan' => $lastScan,
            'findings' => $this->findings($lastScan),
        ];
    }

    /** Store a snapshot (scheduler, every 5 minutes) and prune old ones. */
    /** Overall status computed by the last record() call (with its issues). */
    public ?array $lastOverall = null;

    public function record(): SystemMetric
    {
        $app = $this->application();
        $server = $this->server();
        $this->lastOverall = $this->overall($app, $server);

        $metric = SystemMetric::create([
            'recorded_at' => now(),
            'app_up' => $app['up'],
            'database_ok' => $app['database']['ok'],
            'database_latency_ms' => $app['database']['latency_ms'],
            'queue_pending' => $app['queue']['pending'],
            'queue_failed' => $app['queue']['failed'],
            'scheduler_ok' => $app['scheduler']['ok'],
            'cpu_percent' => $server['load_percent'],
            'memory_percent' => $server['memory']['used_percent'],
            'disk_percent' => $server['disk']['used_percent'],
            'status' => $this->lastOverall['level'],
        ]);

        SystemMetric::where('recorded_at', '<', now()->subDays(SystemMetric::KEEP_DAYS))->delete();

        return $metric;
    }

    /** Overall health cached for a minute, for the navigation badge. */
    public function cachedOverall(): array
    {
        return Cache::remember('monitor:overall', 60, fn () => $this->overall());
    }

    public function server(): array
    {
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : false;
        $cores = $this->cpuCores();
        $memory = $this->memory();

        return [
            'hostname' => gethostname() ?: null,
            'os' => PHP_OS_FAMILY . ' ' . php_uname('r'),
            'cpu_cores' => $cores,
            'load' => $load ? array_map(fn ($v) => round($v, 2), $load) : null,
            'load_percent' => $load && $cores ? round($load[0] / $cores * 100, 1) : null,
            'memory' => $memory,
            'disk' => $this->disk(base_path()),
            'uptime_seconds' => $this->uptime(),
            'container' => $this->container(),
        ];
    }

    private function cpuCores(): ?int
    {
        $info = @file_get_contents('/proc/cpuinfo');

        return $info ? max(1, substr_count($info, "\nprocessor") + (str_starts_with($info, 'processor') ? 1 : 0)) : null;
    }

    /** @return array{total: ?int, available: ?int, used_percent: ?float} */
    private function memory(): array
    {
        $info = @file_get_contents('/proc/meminfo');
        $kb = fn (string $key) => $info && preg_match('/^' . $key . ':\s+(\d+)/m', $info, $m) ? (int) $m[1] * 1024 : null;
        $total = $kb('MemTotal');
        $available = $kb('MemAvailable');

        return [
            'total' => $total,
            'available' => $available,
            'used_percent' => $total && $available !== null ? round(($total - $available) / $total * 100, 1) : null,
        ];
    }

    private function uptime(): ?int
    {
        $uptime = @file_get_contents('/proc/uptime');

        return $uptime ? (int) explode(' ', $uptime)[0] : null;
    }

    /** Docker/containerd details when the app runs in a container (e.g. Coolify). */
    private function container(): ?array
    {
        $cgroup = (string) @file_get_contents('/proc/1/cgroup');
        $inside = file_exists('/.dockerenv') || str_contains($cgroup, 'docker') || str_contains($cgroup, 'kubepods') || str_contains($cgroup, 'containerd');
        if (! $inside) {
            return null;
        }

        $read = fn (string $file) => is_readable($file) ? trim((string) file_get_contents($file)) : null;
        $limit = $read('/sys/fs/cgroup/memory.max');
        $usage = $read('/sys/fs/cgroup/memory.current');

        return [
            'id' => substr((string) gethostname(), 0, 12),
            'memory_usage' => is_numeric($usage) ? (int) $usage : null,
            'memory_limit' => is_numeric($limit) ? (int) $limit : null,
        ];
    }

    public static function bytes(?int $bytes): string
    {
        if ($bytes === null) {
            return '–';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = $bytes > 0 ? min((int) floor(log($bytes, 1024)), count($units) - 1) : 0;

        return str_replace('.', ',', (string) round($bytes / 1024 ** $i, 1)) . ' ' . $units[$i];
    }

    public static function duration(?int $seconds): string
    {
        if ($seconds === null) {
            return '–';
        }
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return trim(($days ? "{$days} hari " : '') . ($hours ? "{$hours} jam " : '') . "{$minutes} menit");
    }
}
