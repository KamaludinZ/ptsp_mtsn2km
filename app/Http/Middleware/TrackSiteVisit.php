<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Counts unique visitors per day for the public footer statistics.
 * Privacy: only a salted, day-scoped hash is stored (never the IP or the
 * user agent), and staff/admin, API and asset requests are not counted.
 */
class TrackSiteVisit
{
    private const IGNORED_PATHS = ['admin*', 'cp*', 'livewire*', 'api/*', 'build/*', 'storage/*', 'up', 'filament*', 'pimpinan*', 'backoffice*', 'frontdesk*', 'supervision*', 'portal*'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            if ($this->shouldCount($request, $response)) {
                $this->record($request);
            }
        } catch (\Throwable $e) {
            report($e); // Never let statistics break a page view.
        }

        return $response;
    }

    private function shouldCount(Request $request, Response $response): bool
    {
        return $request->isMethod('GET')
            && $response->getStatusCode() === 200
            && ! $request->ajax()
            && ! $request->is(...self::IGNORED_PATHS)
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            && ! preg_match('/bot|crawl|spider|slurp|preview|monitor|curl|wget|python|headless/i', (string) $request->userAgent());
    }

    private function record(Request $request): void
    {
        $today = now()->toDateString();
        $hash = hash('sha256', implode('|', [config('app.key'), $today, $request->ip(), $request->userAgent()]));

        // The cache guard keeps this to one DB write per visitor per day.
        if (Cache::add("site_visit:{$hash}", 1, now()->endOfDay())) {
            DB::table('site_visits')->insertOrIgnore([
                'visited_on' => $today,
                'visitor_hash' => $hash,
                'created_at' => now(),
            ]);
            Cache::forget(\App\Support\SiteStats::CACHE_KEY);
        }
    }
}
