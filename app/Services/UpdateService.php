<?php

namespace App\Services;

use App\Exceptions\TicketActionException;
use App\Models\AppUpdate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

/**
 * Update aplikasi: checks the GitHub repository for a newer release and
 * keeps the history of updates (GitHub or uploaded package).
 */
class UpdateService
{
    /** What an update package may replace; everything else is left alone. */
    public const REPLACEABLE = ['app', 'bootstrap', 'config', 'database', 'lang', 'resources', 'routes', 'public', 'artisan', 'composer.json', 'composer.lock', 'package.json'];

    /** Never touched, even if a package contains them. */
    public const FORBIDDEN = ['.env', 'storage/', 'vendor/', '.git/', 'public/storage', 'node_modules/'];

    public const MAX_PACKAGE_KB = 51200;

    /** The code base updates are applied to (overridable in tests). */
    public string $target;

    public function __construct()
    {
        $this->target = base_path();
    }

    public function repository(): string
    {
        return (string) (config('app.update_repository') ?: 'KamaludinZ/ptsp_mtsn2km');
    }

    /**
     * Latest release on GitHub (cached 10 minutes).
     *
     * @return array{tag: string, name: string, published_at: ?string, url: string, notes: string}|array{error: string}
     */
    public function latestRelease(bool $fresh = false): array
    {
        $key = 'update:latest-release';
        if ($fresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, 600, function () {
            try {
                $response = Http::timeout(10)->acceptJson()
                    ->withHeaders(['User-Agent' => 'ptsp-update-check'])
                    ->get('https://api.github.com/repos/' . $this->repository() . '/releases/latest');
            } catch (Throwable $e) {
                return ['error' => 'GitHub tidak dapat dihubungi.'];
            }

            if ($response->status() === 404) {
                return ['error' => 'Belum ada rilis di GitHub untuk ' . $this->repository() . '.'];
            }
            if (! $response->successful()) {
                return ['error' => 'GitHub menjawab HTTP ' . $response->status() . '.'];
            }

            return [
                'tag' => (string) $response->json('tag_name'),
                'name' => (string) ($response->json('name') ?: $response->json('tag_name')),
                'published_at' => $response->json('published_at'),
                'url' => (string) $response->json('html_url'),
                'notes' => mb_strimwidth((string) $response->json('body'), 0, 2000, '…'),
            ];
        });
    }

    /**
     * Is release $tag newer than the running version ("v1.0.0-37-g2b44d7f8" is
     * 37 commits after v1.0.0, so v1.0.0 is not newer)? Null when unknown.
     */
    public function isNewer(string $tag, string $current): ?bool
    {
        $number = fn (string $v) => preg_match('/^v?(\d+(?:\.\d+)*)/', $v, $m) ? $m[1] : null;
        [$release, $running] = [$number($tag), $number($current)];

        return $release && $running ? version_compare($release, $running, '>') : null;
    }

    public function history(int $limit = 20)
    {
        return AppUpdate::with('performer:id,name')->latest()->limit($limit)->get();
    }

    /**
     * Riwayat pembaruan for updates that arrive by redeploy (GitHub/Coolify):
     * when the running version differs from the last one recorded, record it
     * as applied. Uploaded packages keep their own entries and do not change
     * the git version, so they are not compared here.
     */
    public function recordDeployment(string $current): ?AppUpdate
    {
        if ($current === '' || $current === 'tidak diketahui') {
            return null;
        }

        $last = AppUpdate::where('source', 'deploy')->latest()->latest('id')->first();
        if ($last?->to_version === $current) {
            return null;
        }

        return AppUpdate::create([
            'source' => 'deploy',
            'from_version' => $last?->to_version,
            'to_version' => $current,
            'status' => 'applied',
            'notes' => $last ? 'Versi baru terdeteksi setelah redeploy.' : 'Versi pertama yang tercatat.',
            'finished_at' => now(),
        ]);
    }

    /**
     * Check and keep an uploaded update package (ZIP). It must hold the
     * application at its root (composer.json), possibly inside one folder as
     * GitHub archives do, and nothing outside it.
     */
    public function storePackage(UploadedFile $file, User $admin, ?string $checksum = null): AppUpdate
    {
        if ($checksum && ! hash_equals(strtolower($checksum), hash_file('sha256', $file->getRealPath()))) {
            throw new TicketActionException('Checksum SHA-256 tidak cocok: berkas rusak atau bukan paket yang dimaksud.');
        }

        $entries = $this->entries($file->getRealPath());
        $composer = json_decode((string) $this->read($file->getRealPath(), 'composer.json'), true);

        $path = $file->storeAs('app-updates', now()->format('Ymd-His') . '-' . Str::random(6) . '.zip', 'local');

        return AppUpdate::create([
            'source' => 'upload',
            'from_version' => app(SystemMonitorService::class)->version(),
            'to_version' => $composer['version'] ?? ('unggahan ' . now()->format('Y-m-d H:i')),
            'status' => 'pending',
            'package_path' => $path,
            'package_name' => $file->getClientOriginalName(),
            'notes' => count($entries) . ' berkas dalam paket.',
            'performed_by' => $admin->id,
        ]);
    }

    /**
     * Apply a stored package: maintenance mode, back up what will be
     * replaced, copy the package in, migrate; on any error restore the
     * backup. Refused inside a container, where a redeploy would undo it.
     */
    public function apply(AppUpdate $update, User $admin, bool $allowInContainer = false): AppUpdate
    {
        if ($update->status !== 'pending' || $update->source !== 'upload') {
            throw new TicketActionException('Hanya paket unggahan yang belum diterapkan yang dapat diterapkan.');
        }
        if (! $allowInContainer && app(SystemMonitorService::class)->server()['container']) {
            throw new TicketActionException('Aplikasi berjalan di container (Docker/Coolify): perubahan berkas akan hilang saat redeploy. Terapkan pembaruan lewat GitHub/Coolify.');
        }

        $zip = Storage::disk('local')->path($update->package_path);
        $prefix = $this->prefix($zip);

        if ($this->read($zip, 'composer.lock') !== null && is_file($this->target . '/composer.lock')
            && $this->read($zip, 'composer.lock') !== file_get_contents($this->target . '/composer.lock')) {
            return $this->fail($update, $admin, 'Paket mengubah dependensi (composer.lock); jalankan pembaruan lewat redeploy agar composer install ikut berjalan.');
        }

        $backup = $this->backup();
        $live = $this->target === base_path();
        if ($live) {
            Artisan::call('down', ['--retry' => 60]);
        }

        try {
            $archive = new ZipArchive();
            $archive->open($zip);
            for ($i = 0; $i < $archive->numFiles; $i++) {
                $name = substr($archive->getNameIndex($i), strlen($prefix));
                if ($name === '' || str_ends_with($name, '/') || ! $this->replaceable($name)) {
                    continue;
                }
                $destination = $this->target . '/' . $name;
                File::ensureDirectoryExists(dirname($destination));
                file_put_contents($destination, $archive->getFromIndex($i));
            }
            $archive->close();

            if ($live) {
                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('optimize:clear');
            }
        } catch (Throwable $e) {
            $this->restore($backup);

            return $this->fail($update, $admin, 'Gagal diterapkan dan dikembalikan: ' . mb_strimwidth($e->getMessage(), 0, 200, '…'));
        } finally {
            if ($live) {
                Artisan::call('up');
            }
        }

        $update->update(['status' => 'applied', 'finished_at' => now(), 'performed_by' => $admin->id, 'notes' => trim($update->notes . ' Cadangan: ' . basename($backup))]);
        Cache::forget('monitor:version');

        return $update;
    }

    /** @return array<int, string> package entries (without the archive's top folder) */
    private function entries(string $zip): array
    {
        $archive = new ZipArchive();
        if ($archive->open($zip) !== true) {
            throw new TicketActionException('Berkas bukan arsip ZIP yang valid.');
        }

        $names = [];
        for ($i = 0; $i < $archive->numFiles; $i++) {
            $names[] = $archive->getNameIndex($i);
        }
        $archive->close();

        foreach ($names as $name) {
            if (in_array('..', explode('/', $name), true) || str_starts_with($name, '/') || str_contains($name, '\\') || str_contains($name, "\0") || preg_match('/^[A-Za-z]:/', $name)) {
                throw new TicketActionException('Paket berisi jalur berkas yang tidak aman: ' . $name);
            }
        }

        $prefix = $this->prefixOf($names);
        $relative = array_values(array_filter(array_map(fn ($n) => substr($n, strlen($prefix)), $names)));

        if (! in_array('composer.json', $relative, true)) {
            throw new TicketActionException('Paket tidak berisi aplikasi (composer.json tidak ditemukan di akar).');
        }

        return $relative;
    }

    /** GitHub archives wrap everything in one folder, e.g. "ptsp_mtsn2km-v1.2.0/". */
    private function prefixOf(array $names): string
    {
        $first = explode('/', $names[0] ?? '')[0] . '/';

        return $first !== '/' && collect($names)->every(fn ($n) => str_starts_with($n, $first)) ? $first : '';
    }

    private function prefix(string $zip): string
    {
        $archive = new ZipArchive();
        $archive->open($zip);
        $names = [];
        for ($i = 0; $i < $archive->numFiles; $i++) {
            $names[] = $archive->getNameIndex($i);
        }
        $archive->close();

        return $this->prefixOf($names);
    }

    private function read(string $zip, string $relative): ?string
    {
        $archive = new ZipArchive();
        $archive->open($zip);
        $content = $archive->getFromName($this->prefix($zip) . $relative);
        $archive->close();

        return $content === false ? null : $content;
    }

    private function replaceable(string $name): bool
    {
        foreach (self::FORBIDDEN as $forbidden) {
            if ($name === rtrim($forbidden, '/') || str_starts_with($name, $forbidden)) {
                return false;
            }
        }

        return in_array(explode('/', $name)[0], self::REPLACEABLE, true);
    }

    /** ZIP of everything an update may replace, kept in storage/app/update-backups. */
    private function backup(): string
    {
        $path = Storage::disk('local')->path('update-backups/' . now()->format('Ymd-His') . '-' . Str::random(4) . '.zip');
        File::ensureDirectoryExists(dirname($path));

        $archive = new ZipArchive();
        $archive->open($path, ZipArchive::CREATE);
        foreach (self::REPLACEABLE as $item) {
            $full = $this->target . '/' . $item;
            if (is_file($full)) {
                $archive->addFile($full, $item);
            } elseif (is_dir($full)) {
                foreach (File::allFiles($full) as $file) {
                    $relative = $item . '/' . str_replace('\\', '/', $file->getRelativePathname());
                    if ($this->replaceable($relative)) {
                        $archive->addFile($file->getPathname(), $relative);
                    }
                }
            }
        }
        $archive->close();

        return $path;
    }

    private function restore(string $backup): void
    {
        $archive = new ZipArchive();
        if ($archive->open($backup) === true) {
            $archive->extractTo($this->target);
            $archive->close();
        }
    }

    private function fail(AppUpdate $update, User $admin, string $reason): AppUpdate
    {
        $update->update(['status' => 'failed', 'finished_at' => now(), 'performed_by' => $admin->id, 'notes' => $reason]);

        return $update;
    }
}
