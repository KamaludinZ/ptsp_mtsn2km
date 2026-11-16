<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Finder\Finder;
use Throwable;

/**
 * Laravel removes an expired cache entry only when that same key is read
 * again. Keys that are never read again pile up for good: the per-minute
 * request counter per IP (LogSuspiciousActivity, file store) and the daily
 * visitor marks (TrackSiteVisit, cache table). This drops them every night,
 * so the cache folder and table stay small however many visitors come.
 */
class PruneExpiredCache extends Command
{
    protected $signature = 'cache:prune-expired';

    protected $description = 'Hapus entri cache yang sudah kedaluwarsa (tabel cache dan folder cache berkas)';

    public function handle(): int
    {
        $rows = $this->pruneTable();
        $files = $this->pruneFiles((string) config('cache.stores.file.path'));

        $this->info("{$rows} baris cache database dan {$files} berkas cache kedaluwarsa dihapus.");

        return self::SUCCESS;
    }

    private function pruneTable(): int
    {
        $table = (string) config('cache.stores.database.table', 'cache');

        try {
            if (! Schema::hasTable($table)) {
                return 0;
            }

            $deleted = 0;
            // In batches, so a large backlog never holds a long lock.
            do {
                $ids = DB::table($table)->where('expiration', '<', now()->getTimestamp())->limit(5000)->pluck('key');
                $deleted += $ids->isEmpty() ? 0 : DB::table($table)->whereIn('key', $ids)->delete();
            } while ($ids->count() === 5000);

            return $deleted;
        } catch (Throwable $e) {
            $this->warn('Tabel cache tidak dapat dibersihkan: ' . $e->getMessage());

            return 0;
        }
    }

    /** A file store entry starts with its expiry as a 10-digit Unix time. */
    private function pruneFiles(string $path): int
    {
        if ($path === '' || ! is_dir($path)) {
            return 0;
        }

        $now = now()->getTimestamp();
        $deleted = 0;

        foreach (Finder::create()->files()->in($path)->ignoreDotFiles(true) as $file) {
            $handle = @fopen($file->getPathname(), 'r');
            if (! $handle) {
                continue;
            }
            $expires = fread($handle, 10);
            fclose($handle);

            if (is_string($expires) && ctype_digit($expires) && (int) $expires < $now && @unlink($file->getPathname())) {
                $deleted++;
            }
        }

        // Folders the store made for the deleted entries (two levels of hash).
        foreach (array_reverse(iterator_to_array(Finder::create()->directories()->in($path), false)) as $dir) {
            @rmdir($dir->getPathname()); // fails, harmlessly, while not empty
        }

        return $deleted;
    }
}
