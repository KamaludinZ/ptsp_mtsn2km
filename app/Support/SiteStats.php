<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SiteStats
{
    public const CACHE_KEY = 'site_stats_visitors';

    /**
     * Unique visitors: today, this month and all time.
     *
     * @return array{today:int, month:int, total:int}
     */
    public static function visitors(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            $today = now()->toDateString();

            return [
                'today' => (int) DB::table('site_visits')->where('visited_on', $today)->count(),
                'month' => (int) DB::table('site_visits')->where('visited_on', '>=', now()->startOfMonth()->toDateString())->count(),
                'total' => (int) DB::table('site_visits')->count(),
            ];
        });
    }
}
