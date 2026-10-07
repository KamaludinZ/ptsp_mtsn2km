<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckBlockedIP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        // Skip IP blocking for localhost during development
        if (app()->environment('local', 'development') && in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return $next($request);
        }

        // Active blocks from the blocked_ips table (cached briefly); expired ones never match.
        if ($blockData = app(\App\Support\SecurityMonitor::class)->isBlocked($ip)) {
            Log::warning('Blocked IP attempted access', [
                'ip' => $ip,
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->view('errors.blocked', [
                'reason' => $blockData['reason'] ?? 'Your IP address has been blocked.',
                'expires_at' => $blockData['expires_at'],
            ], 403);
        }

        return $next($request);
    }
}
