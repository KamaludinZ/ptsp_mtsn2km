<?php

namespace App\Services;

use App\Models\AppUpdate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Update aplikasi: checks the GitHub repository for a newer release and
 * keeps the history of updates (GitHub or uploaded package).
 */
class UpdateService
{
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
}
