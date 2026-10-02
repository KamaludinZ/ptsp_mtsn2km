<?php

namespace App\Support;

/**
 * Reads the most recent entries of the application log for the monitoring
 * page: newest first, message only (no stack traces), bounded in size.
 */
class LogReader
{
    public const LEVELS = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];

    private const TAIL_BYTES = 512 * 1024;

    /** Newest log file (laravel.log or laravel-YYYY-MM-DD.log). */
    public static function latestFile(): ?string
    {
        $files = glob(storage_path('logs/laravel*.log')) ?: [];
        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        return $files[0] ?? null;
    }

    /**
     * @return array<int, array{at: string, level: string, message: string}>
     */
    public static function entries(?string $level = null, ?string $search = null, int $limit = 100): array
    {
        $file = self::latestFile();
        if (! $file || ! is_readable($file)) {
            return [];
        }

        $size = filesize($file);
        $handle = fopen($file, 'r');
        fseek($handle, max(0, $size - self::TAIL_BYTES));
        $content = stream_get_contents($handle);
        fclose($handle);

        preg_match_all('/^\[(\d{4}-\d{2}-\d{2}[ T][\d:.+\-]+)\] \w+\.(\w+): (.*)$/m', $content, $matches, PREG_SET_ORDER);

        $entries = [];
        foreach (array_reverse($matches) as [, $at, $entryLevel, $message]) {
            $entryLevel = strtolower($entryLevel);
            if ($level && $entryLevel !== $level) {
                continue;
            }
            if ($search && ! str_contains(mb_strtolower($message), mb_strtolower($search))) {
                continue;
            }
            $entries[] = ['at' => $at, 'level' => $entryLevel, 'message' => mb_strimwidth($message, 0, 400, '…')];
            if (count($entries) >= $limit) {
                break;
            }
        }

        return $entries;
    }

    public static function color(string $level): string
    {
        return match ($level) {
            'emergency', 'alert', 'critical', 'error' => 'danger',
            'warning' => 'warning',
            'notice', 'info' => 'info',
            default => 'gray',
        };
    }
}
