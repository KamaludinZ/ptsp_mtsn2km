<?php

namespace App\Support;

use App\Models\Pengumuman;
use Illuminate\Database\Eloquent\Builder;

/**
 * Status tayang pengumuman: draf (switched off), dijadwalkan (publish date
 * still ahead), tayang (on the site now) or berakhir (end date passed).
 */
class ContentStatus
{
    public const LABELS = ['tayang' => 'Tayang', 'dijadwalkan' => 'Dijadwalkan', 'draf' => 'Draf', 'berakhir' => 'Berakhir'];

    public const COLORS = ['tayang' => 'success', 'dijadwalkan' => 'info', 'draf' => 'gray', 'berakhir' => 'warning'];

    public static function of(Pengumuman $item): string
    {
        return match (true) {
            ! $item->is_active => 'draf',
            $item->publish_date && $item->publish_date->isAfter(today()) => 'dijadwalkan',
            $item->end_date && $item->end_date->isBefore(today()) => 'berakhir',
            default => 'tayang',
        };
    }

    public static function scope(Builder $query, string $status): Builder
    {
        return match ($status) {
            'draf' => $query->where('is_active', false),
            'dijadwalkan' => $query->where('is_active', true)->whereDate('publish_date', '>', today()),
            'berakhir' => $query->where('is_active', true)->whereDate('end_date', '<', today()),
            default => $query->where('is_active', true)
                ->where(fn (Builder $q) => $q->whereNull('publish_date')->orWhereDate('publish_date', '<=', today()))
                ->where(fn (Builder $q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today())),
        };
    }

    /** Show it on the site from today (keeps a later end date). */
    public static function publish(Pengumuman $item): void
    {
        $item->update([
            'is_active' => true,
            'publish_date' => $item->publish_date && $item->publish_date->lte(today()) ? $item->publish_date : today(),
            'end_date' => $item->end_date && $item->end_date->lt(today()) ? null : $item->end_date,
        ]);
    }

    /** Stop showing it, keeping it as an archive (end date = yesterday). */
    public static function end(Pengumuman $item): void
    {
        $item->update([
            'end_date' => today()->subDay(),
            'publish_date' => $item->publish_date && $item->publish_date->lte(today()->subDay()) ? $item->publish_date : today()->subDay(),
        ]);
    }

    /** @return array<string, int> */
    public static function counts(): array
    {
        return collect(array_keys(self::LABELS))->mapWithKeys(fn (string $s) => [$s => self::scope(Pengumuman::query(), $s)->count()])->all();
    }
}
