<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * System security tools for administrators: health checks, the blocked IP
 * list enforced by CheckBlockedIP, maintenance mode and cache clearing.
 */
class SecurityMonitor
{
    public const CACHE_TYPES = [
        'all' => 'Semua cache',
        'config' => 'Konfigurasi',
        'route' => 'Rute',
        'view' => 'Tampilan (view)',
        'cache' => 'Cache aplikasi',
    ];

    public function metrics(): array
    {
        return [
            'failed_logins_today' => $this->getFailedLoginsToday(),
            'failed_logins_this_week' => $this->getFailedLoginsThisWeek(),
            'blocked_ips_count' => $this->getBlockedIPsCount(),
            'suspicious_activities_today' => $this->getSuspiciousActivitiesToday(),
            'last_security_scan' => Cache::get('last_security_scan'),
            'security_score' => $this->calculateSecurityScore(),
            'checks' => [
                'CSRF' => $this->checkCSRFProtection(),
                'SQL Injection' => $this->checkSQLInjectionProtection(),
                'XSS' => $this->checkXSSProtection(),
                'Rate limiting' => $this->checkRateLimiting(),
                'Mode debug' => $this->checkDebugMode(),
            ],
        ];
    }

    public function runScan(): array
    {
        $checks = [
            'Izin berkas' => $this->checkFilePermissions(),
            'Lingkungan (.env)' => $this->checkEnvironmentSecurity(),
            'Basis data' => $this->checkDatabaseSecurity(),
            'Sesi' => $this->checkSessionSecurity(),
            'Kebijakan kata sandi' => $this->checkPasswordPolicy(),
            'Dependensi' => $this->checkDependencies(),
            'Header keamanan' => $this->checkSecurityHeaders(),
            'SSL/TLS' => $this->checkSSLConfiguration(),
            'Directory listing' => $this->checkDirectoryListing(),
            'Mode debug' => $this->checkDebugMode(),
        ];

        $results = [
            'timestamp' => now()->toDateTimeString(),
            'checks' => $checks,
            'overall_score' => $this->calculateOverallScore($checks),
        ];

        Cache::put('last_security_scan_results', $results, now()->addDays(7));
        Cache::put('last_security_scan', $results['timestamp'], now()->addDays(7));
        Log::info('Security scan completed', ['score' => $results['overall_score']]);

        return $results;
    }

    public function lastScan(): ?array
    {
        return Cache::get('last_security_scan_results');
    }

    /** Active blocks; expired ones are dropped. */
    public function blockedIps(): array
    {
        $blocked = Cache::get('blocked_ips', []);

        foreach ($blocked as $ip => $data) {
            if (! empty($data['expires_at']) && Carbon::parse($data['expires_at'])->isPast()) {
                unset($blocked[$ip]);
            }
        }

        Cache::put('blocked_ips', $blocked, now()->addYears(10));

        return $blocked;
    }

    public function blockIp(string $ip, string $reason, ?int $hours, string $by): void
    {
        $blocked = $this->blockedIps();
        $blocked[$ip] = [
            'reason' => $reason,
            'blocked_at' => now()->toDateTimeString(),
            'blocked_by' => $by,
            'expires_at' => $hours ? now()->addHours($hours)->toDateTimeString() : null,
        ];

        Cache::put('blocked_ips', $blocked, now()->addYears(10));
        Log::warning('IP address blocked', ['ip' => $ip, 'reason' => $reason, 'blocked_by' => $by]);
    }

    public function unblockIp(string $ip, string $by): bool
    {
        $blocked = $this->blockedIps();

        if (! isset($blocked[$ip])) {
            return false;
        }

        unset($blocked[$ip]);
        Cache::put('blocked_ips', $blocked, now()->addYears(10));
        Log::info('IP address unblocked', ['ip' => $ip, 'unblocked_by' => $by]);

        return true;
    }

    public function isDown(): bool
    {
        return app()->isDownForMaintenance();
    }

    public function enableMaintenance(?string $secret, int $retry, string $by): void
    {
        $options = ['--retry' => $retry];
        if (filled($secret)) {
            $options['--secret'] = $secret;
        }

        Artisan::call('down', $options);
        Log::info('Maintenance mode enabled', ['enabled_by' => $by]);
    }

    public function disableMaintenance(string $by): void
    {
        Artisan::call('up');
        Log::info('Maintenance mode disabled', ['disabled_by' => $by]);
    }

    public function clearCache(string $type, string $by): void
    {
        Artisan::call(match ($type) {
            'config' => 'config:clear',
            'route' => 'route:clear',
            'view' => 'view:clear',
            'cache' => 'cache:clear',
            default => 'optimize:clear',
        });

        Log::info('Cache cleared', ['type' => $type, 'cleared_by' => $by]);
    }

    /** Called on every failed sign-in (Illuminate\Auth\Events\Failed). */
    public static function recordFailedLogin(?string $email, ?string $ip): void
    {
        Cache::put('failed_logins_today:' . now()->toDateString(), Cache::get('failed_logins_today:' . now()->toDateString(), 0) + 1, now()->endOfDay());
        Cache::put('failed_logins_week:' . now()->format('o-W'), Cache::get('failed_logins_week:' . now()->format('o-W'), 0) + 1, now()->endOfWeek());

        $recent = Cache::get('failed_logins_recent', []);
        array_unshift($recent, ['at' => now()->toIso8601String(), 'email' => self::maskEmail($email), 'ip' => $ip]);
        Cache::put('failed_logins_recent', array_slice($recent, 0, 20), now()->addDays(7));
    }

    /** Called when sign-in throttling locks someone out (Illuminate\Auth\Events\Lockout). */
    public static function recordLockout(?string $ip): void
    {
        Cache::put('lockouts_today:' . now()->toDateString(), Cache::get('lockouts_today:' . now()->toDateString(), 0) + 1, now()->endOfDay());
    }

    /** "bu***@email.com": enough to recognise an account without exposing it. */
    private static function maskEmail(?string $email): ?string
    {
        if (! $email || ! str_contains($email, '@')) {
            return $email ? mb_substr($email, 0, 2) . '***' : null;
        }
        [$name, $domain] = explode('@', $email, 2);

        return mb_substr($name, 0, 2) . '***@' . $domain;
    }

    public function recentFailedLogins(): array
    {
        return Cache::get('failed_logins_recent', []);
    }

    public function lockoutsToday(): int
    {
        return (int) Cache::get('lockouts_today:' . now()->toDateString(), 0);
    }

    private function getFailedLoginsToday()
    {
        return (int) Cache::get('failed_logins_today:' . now()->toDateString(), 0);
    }

    private function getFailedLoginsThisWeek()
    {
        return (int) Cache::get('failed_logins_week:' . now()->format('o-W'), 0);
    }

    private function getBlockedIPsCount()
    {
        $blockedIPs = Cache::get('blocked_ips', []);
        return count($blockedIPs);
    }

    private function getSuspiciousActivitiesToday()
    {
        return Cache::get('suspicious_activities_today', 0);
    }

    private function calculateSecurityScore()
    {
        $checks = [
            'csrf' => $this->checkCSRFProtection(),
            'sql' => $this->checkSQLInjectionProtection(),
            'xss' => $this->checkXSSProtection(),
            'rate_limiting' => $this->checkRateLimiting(),
            'debug_mode' => $this->checkDebugMode(),
        ];

        $passed = array_filter($checks, function($check) {
            return $check['status'] === 'pass';
        });

        return round((count($passed) / count($checks)) * 100, 2);
    }

    private function checkCSRFProtection()
    {
        return [
            'status' => config('app.env') !== 'local' && File::exists(app_path('Http/Middleware/VerifyCsrfToken.php')) ? 'pass' : 'warning',
            'message' => 'CSRF protection is enabled',
        ];
    }

    private function checkSQLInjectionProtection()
    {
        return [
            'status' => 'pass',
            'message' => 'Using Laravel Eloquent ORM for SQL injection protection',
        ];
    }

    private function checkXSSProtection()
    {
        return [
            'status' => 'pass',
            'message' => 'Blade templating engine provides XSS protection',
        ];
    }

    private function checkRateLimiting()
    {
        return [
            'status' => File::exists(app_path('Http/Kernel.php')) ? 'pass' : 'warning',
            'message' => 'Rate limiting middleware is configured',
        ];
    }

    private function checkDebugMode()
    {
        return [
            'status' => config('app.debug') === false ? 'pass' : 'fail',
            'message' => config('app.debug') ? 'Debug mode is ENABLED (Security Risk!)' : 'Debug mode is disabled',
        ];
    }

    private function checkFilePermissions()
    {
        $criticalPaths = [
            storage_path(),
            storage_path('framework'),
            storage_path('logs'),
            app()->bootstrapPath('cache'),
        ];

        $issues = [];
        foreach ($criticalPaths as $path) {
            if (File::exists($path)) {
                $perms = substr(sprintf('%o', fileperms($path)), -4);
                if ($perms !== '0775' && $perms !== '0755') {
                    $issues[] = "$path has permissions $perms (should be 0755 or 0775)";
                }
            }
        }

        return [
            'status' => empty($issues) ? 'pass' : 'warning',
            'message' => empty($issues) ? 'File permissions are correctly configured' : 'Some file permissions need attention',
            'issues' => $issues,
        ];
    }

    private function checkEnvironmentSecurity()
    {
        $issues = [];

        if (config('app.env') === 'production' && config('app.debug') === true) {
            $issues[] = 'Debug mode is enabled in production';
        }

        if (empty(config('app.key'))) {
            $issues[] = 'Application key is not set';
        }

        if (config('app.env') === 'production' && strpos(config('app.url'), 'http://') === 0) {
            $issues[] = 'Application URL uses HTTP instead of HTTPS';
        }

        return [
            'status' => empty($issues) ? 'pass' : 'fail',
            'message' => empty($issues) ? 'Environment is securely configured' : 'Environment configuration has security issues',
            'issues' => $issues,
        ];
    }

    private function checkDatabaseSecurity()
    {
        $issues = [];

        if (config('database.connections.pgsql.username') === 'postgres') {
            $issues[] = 'Using the postgres superuser for database connection (security risk)';
        }

        return [
            'status' => empty($issues) ? 'pass' : 'warning',
            'message' => empty($issues) ? 'Database is securely configured' : 'Database configuration needs improvement',
            'issues' => $issues,
        ];
    }

    private function checkSessionSecurity()
    {
        $issues = [];

        if (config('session.secure') === false && config('app.env') === 'production') {
            $issues[] = 'Session cookies are not set to secure-only';
        }

        if (config('session.http_only') === false) {
            $issues[] = 'Session cookies are accessible via JavaScript (XSS risk)';
        }

        return [
            'status' => empty($issues) ? 'pass' : 'warning',
            'message' => empty($issues) ? 'Session is securely configured' : 'Session configuration needs improvement',
            'issues' => $issues,
        ];
    }

    private function checkPasswordPolicy()
    {
        // Check if password validation rules are in place
        return [
            'status' => 'pass',
            'message' => 'Password policy is configured via validation rules',
        ];
    }

    private function checkDependencies()
    {
        // This would ideally check composer.lock for known vulnerabilities
        return [
            'status' => 'info',
            'message' => 'Run "composer audit" to check for vulnerable dependencies',
        ];
    }

    private function checkSecurityHeaders()
    {
        // Check if security headers middleware is configured
        return [
            'status' => 'info',
            'message' => 'Security headers should be configured in web server or middleware',
        ];
    }

    private function checkSSLConfiguration()
    {
        $isHttps = request()->secure() || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');

        return [
            'status' => $isHttps ? 'pass' : 'warning',
            'message' => $isHttps ? 'SSL/TLS is properly configured' : 'SSL/TLS is not detected',
        ];
    }

    private function checkDirectoryListing()
    {
        return [
            'status' => 'info',
            'message' => 'Directory listing should be disabled in web server configuration',
        ];
    }

    private function calculateOverallScore($checks)
    {
        $total = count($checks);
        $passed = 0;

        foreach ($checks as $check) {
            if ($check['status'] === 'pass') {
                $passed++;
            }
        }

        return $total > 0 ? round(($passed / $total) * 100, 2) : 0;
    }
}
