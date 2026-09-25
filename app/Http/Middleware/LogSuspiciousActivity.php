<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LogSuspiciousActivity
{
    /**
     * Patterns that indicate potential security threats
     */
    private $suspiciousPatterns = [
        // SQL Injection patterns
        '/(\bunion\b.*\bselect\b)/i',
        '/(\bselect\b.*\bfrom\b)/i',
        '/(\binsert\b.*\binto\b)/i',
        '/(\bdelete\b.*\bfrom\b)/i',
        '/(\bdrop\b.*\btable\b)/i',
        '/(\bupdate\b.*\bset\b)/i',
        '/(\'.*or.*\'.*=.*\')/i',

        // XSS patterns
        '/(<script[^>]*>.*<\/script>)/i',
        '/(<iframe[^>]*>)/i',
        '/(<object[^>]*>)/i',
        '/(<embed[^>]*>)/i',
        '/(javascript:)/i',
        '/(onerror=)/i',
        '/(onload=)/i',

        // Path traversal
        '/(\.\.\/)/i',
        '/(\.\.\\\\)/i',

        // Command injection

        // File inclusion
        '/(php:\/\/)/i',
        '/(file:\/\/)/i',
        '/(data:\/\/)/i',
    ];

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

        // Skip security checks for localhost during development
        if (app()->environment('local', 'development') && in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return $next($request);
        }

        $suspicious = false;
        $matchedPatterns = [];

        // Check URL for suspicious patterns
        $url = $request->fullUrl();
        foreach ($this->suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                $suspicious = true;
                $matchedPatterns[] = $pattern;
            }
        }

        // Check all input data for suspicious patterns
        // Form input is free text (complaints, survey answers, notes), so it
        // is only logged: it never counts towards blocking the IP.
        $allInput = $request->all();
        $logOnly = [];
        foreach ($allInput as $key => $value) {
            if (is_string($value)) {
                foreach ($this->suspiciousPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $logOnly[] = "$key: $pattern";
                    }
                }
            }
        }

        // Check for unusual request methods
        if (!in_array($request->method(), ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'])) {
            $suspicious = true;
            $matchedPatterns[] = 'Unusual HTTP method: ' . $request->method();
        }

        // Check for missing User-Agent (common in bot attacks)
        if (empty($request->userAgent())) {
            $logOnly[] = 'Missing User-Agent';
        }

        // Check for excessive request rate from same IP
        $requestKey = 'request_count_' . $ip;
        $requestCount = Cache::get($requestKey, 0);

        // Many users share the school's public IP, so a high request rate is
        // logged only; per-route rate limiters protect the forms.
        if ($requestCount > 300) {
            $logOnly[] = 'High request rate: ' . $requestCount . ' requests/minute';
        }

        Cache::put($requestKey, $requestCount + 1, now()->addMinutes(1));

        // Log suspicious activity
        if (! $suspicious && $logOnly) {
            Log::info('Unusual request (not blocked)', ['ip' => $ip, 'url' => $url, 'signals' => $logOnly]);
        }

        if ($suspicious) {
            $redactedInput = collect($allInput)->map(function ($value, $key) {
                return preg_match('/password|token|secret|captcha/i', (string) $key)
                    ? '[REDACTED]'
                    : $value;
            })->all();

            Log::warning('Suspicious activity detected', [
                'ip' => $ip,
                'url' => $url,
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'patterns' => $matchedPatterns,
                'input' => $redactedInput,
            ]);

            // Increment daily counter
            $todayKey = 'suspicious_activities_today_' . now()->format('Y-m-d');
            $count = Cache::get($todayKey, 0);
            Cache::put($todayKey, $count + 1, now()->endOfDay());
            Cache::put('suspicious_activities_today', $count + 1, now()->endOfDay());

            // Store recent security logs
            $logs = Cache::get('recent_security_logs', []);
            array_unshift($logs, [
                'timestamp' => now()->toDateTimeString(),
                'ip' => $ip,
                'url' => $url,
                'type' => 'suspicious_activity',
                'patterns' => $matchedPatterns,
            ]);
            $logs = array_slice($logs, 0, 100); // Keep only last 100 logs
            Cache::put('recent_security_logs', $logs, now()->addDays(7));

            // Auto-block IP if too many suspicious activities
            $suspiciousKey = 'suspicious_count_' . $ip;
            $suspiciousCount = Cache::get($suspiciousKey, 0);
            Cache::put($suspiciousKey, $suspiciousCount + 1, now()->addHours(1));

            // Attack signatures in the URL only; signed-in users are never auto-blocked
            if ($suspiciousCount + 1 >= 10 && ! $request->user()) { // 10 in 1 hour
                $this->autoBlockIP($ip, 'Auto-blocked due to excessive suspicious activities');
            }
        }

        return $next($request);
    }

    /**
     * Auto-block an IP address
     */
    private function autoBlockIP($ip, $reason)
    {
        $blockedIPs = Cache::get('blocked_ips', []);

        if (!isset($blockedIPs[$ip])) {
            $blockedIPs[$ip] = [
                'reason' => $reason,
                'blocked_at' => now()->toDateTimeString(),
                'blocked_by' => 'System (Auto-block)',
                'expires_at' => now()->addHours(24)->toDateTimeString(), // Block for 24 hours
            ];

            Cache::put('blocked_ips', $blockedIPs, now()->addYears(10));

            Log::critical('IP auto-blocked due to suspicious activity', [
                'ip' => $ip,
                'reason' => $reason,
            ]);
        }
    }
}
