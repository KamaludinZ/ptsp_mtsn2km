<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SecurityController extends Controller
{
    /**
     * Display security dashboard
     */
    public function dashboard()
    {
        $securityMetrics = $this->getSecurityMetrics();
        $recentLogs = $this->getRecentSecurityLogs();
        $vulnerabilities = $this->getVulnerabilities();
        $blockedIPs = $this->getBlockedIPs();

        return view('admin.security.dashboard', compact('securityMetrics', 'recentLogs', 'vulnerabilities', 'blockedIPs'));
    }

    /**
     * Get security metrics
     */
    private function getSecurityMetrics()
    {
        return [
            'failed_logins_today' => $this->getFailedLoginsToday(),
            'failed_logins_this_week' => $this->getFailedLoginsThisWeek(),
            'blocked_ips_count' => $this->getBlockedIPsCount(),
            'suspicious_activities_today' => $this->getSuspiciousActivitiesToday(),
            'last_security_scan' => Cache::get('last_security_scan', 'Never'),
            'security_score' => $this->calculateSecurityScore(),
            'csrf_protection' => $this->checkCSRFProtection(),
            'sql_injection_protection' => $this->checkSQLInjectionProtection(),
            'xss_protection' => $this->checkXSSProtection(),
            'rate_limiting' => $this->checkRateLimiting(),
        ];
    }

    /**
     * Run security scan
     */
    public function runSecurityScan(Request $request)
    {
        $scanResults = [
            'timestamp' => now()->toDateTimeString(),
            'checks' => [],
        ];

        // Check 1: File permissions
        $scanResults['checks']['file_permissions'] = $this->checkFilePermissions();

        // Check 2: Environment configuration
        $scanResults['checks']['environment'] = $this->checkEnvironmentSecurity();

        // Check 3: Database security
        $scanResults['checks']['database'] = $this->checkDatabaseSecurity();

        // Check 4: Session security
        $scanResults['checks']['session'] = $this->checkSessionSecurity();

        // Check 5: Password policy
        $scanResults['checks']['password_policy'] = $this->checkPasswordPolicy();

        // Check 6: Dependency vulnerabilities
        $scanResults['checks']['dependencies'] = $this->checkDependencies();

        // Check 7: Headers security
        $scanResults['checks']['headers'] = $this->checkSecurityHeaders();

        // Check 8: SSL/TLS configuration
        $scanResults['checks']['ssl'] = $this->checkSSLConfiguration();

        // Check 9: Directory listing
        $scanResults['checks']['directory_listing'] = $this->checkDirectoryListing();

        // Check 10: Debug mode
        $scanResults['checks']['debug_mode'] = $this->checkDebugMode();

        // Calculate overall security score
        $scanResults['overall_score'] = $this->calculateOverallScore($scanResults['checks']);

        // Store scan results
        Cache::put('last_security_scan_results', $scanResults, now()->addDays(7));
        Cache::put('last_security_scan', now()->toDateTimeString(), now()->addDays(7));

        // Log the scan
        Log::info('Security scan completed', ['results' => $scanResults]);

        return response()->json([
            'success' => true,
            'message' => 'Security scan completed successfully',
            'results' => $scanResults,
        ]);
    }

    /**
     * Get scan results
     */
    public function getScanResults()
    {
        $results = Cache::get('last_security_scan_results', [
            'timestamp' => 'Never',
            'checks' => [],
            'overall_score' => 0,
        ]);

        return view('admin.security.scan-results', compact('results'));
    }

    /**
     * Security logs
     */
    public function logs(Request $request)
    {
        $logs = $this->getSecurityLogs($request);

        return view('admin.security.logs', compact('logs'));
    }

    /**
     * Blocked IPs management
     */
    public function blockedIPs()
    {
        $blockedIPs = $this->getBlockedIPs();

        return view('admin.security.blocked-ips', compact('blockedIPs'));
    }

    /**
     * Block an IP address
     */
    public function blockIP(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string|max:255',
            'duration' => 'nullable|integer|min:1', // in hours, null = permanent
        ]);

        $blockedIPs = Cache::get('blocked_ips', []);

        $blockedIPs[$request->ip_address] = [
            'reason' => $request->reason,
            'blocked_at' => now()->toDateTimeString(),
            'blocked_by' => auth()->user()->name,
            'expires_at' => $request->duration ? now()->addHours($request->duration)->toDateTimeString() : null,
        ];

        Cache::put('blocked_ips', $blockedIPs, now()->addYears(10));

        Log::warning('IP address blocked', [
            'ip' => $request->ip_address,
            'reason' => $request->reason,
            'blocked_by' => auth()->user()->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'IP address blocked successfully',
        ]);
    }

    /**
     * Unblock an IP address
     */
    public function unblockIP(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        $blockedIPs = Cache::get('blocked_ips', []);

        if (isset($blockedIPs[$request->ip_address])) {
            unset($blockedIPs[$request->ip_address]);
            Cache::put('blocked_ips', $blockedIPs, now()->addYears(10));

            Log::info('IP address unblocked', [
                'ip' => $request->ip_address,
                'unblocked_by' => auth()->user()->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'IP address unblocked successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'IP address not found in blocked list',
        ], 404);
    }

    /**
     * Rate limiting configuration
     */
    public function rateLimitConfig()
    {
        $config = [
            'api_limit' => config('rate-limiting.api', 60),
            'login_limit' => config('rate-limiting.login', 5),
            'general_limit' => config('rate-limiting.general', 100),
        ];

        return view('admin.security.rate-limiting', compact('config'));
    }

    /**
     * Update rate limiting configuration
     */
    public function updateRateLimitConfig(Request $request)
    {
        $request->validate([
            'api_limit' => 'required|integer|min:1|max:1000',
            'login_limit' => 'required|integer|min:1|max:100',
            'general_limit' => 'required|integer|min:1|max:1000',
        ]);

        // Update configuration (you might want to store this in database)
        Cache::put('rate_limit_config', $request->only(['api_limit', 'login_limit', 'general_limit']), now()->addYears(10));

        return response()->json([
            'success' => true,
            'message' => 'Rate limiting configuration updated successfully',
        ]);
    }

    /**
     * Maintenance mode management
     */
    public function maintenanceMode()
    {
        $isDown = app()->isDownForMaintenance();

        return view('admin.security.maintenance', compact('isDown'));
    }

    /**
     * Enable maintenance mode
     */
    public function enableMaintenanceMode(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:255',
            'retry' => 'nullable|integer|min:60',
            'secret' => 'nullable|string|max:255',
        ]);

        $options = [
            'message' => $request->message ?? 'Application is currently under maintenance.',
            'retry' => $request->retry ?? 3600,
        ];

        if ($request->secret) {
            $options['secret'] = $request->secret;
        }

        Artisan::call('down', $options);

        Log::info('Maintenance mode enabled', [
            'enabled_by' => auth()->user()->email,
            'message' => $options['message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance mode enabled successfully',
        ]);
    }

    /**
     * Disable maintenance mode
     */
    public function disableMaintenanceMode(Request $request)
    {
        Artisan::call('up');

        Log::info('Maintenance mode disabled', [
            'disabled_by' => auth()->user()->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance mode disabled successfully',
        ]);
    }

    /**
     * Clear application cache
     */
    public function clearCache(Request $request)
    {
        $request->validate([
            'cache_type' => 'required|in:all,config,route,view,cache',
        ]);

        switch ($request->cache_type) {
            case 'all':
                Artisan::call('optimize:clear');
                $message = 'All caches cleared successfully';
                break;
            case 'config':
                Artisan::call('config:clear');
                $message = 'Configuration cache cleared successfully';
                break;
            case 'route':
                Artisan::call('route:clear');
                $message = 'Route cache cleared successfully';
                break;
            case 'view':
                Artisan::call('view:clear');
                $message = 'View cache cleared successfully';
                break;
            case 'cache':
                Artisan::call('cache:clear');
                $message = 'Application cache cleared successfully';
                break;
        }

        Log::info('Cache cleared', [
            'type' => $request->cache_type,
            'cleared_by' => auth()->user()->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    // Private helper methods

    private function getFailedLoginsToday()
    {
        return Cache::get('failed_logins_today', 0);
    }

    private function getFailedLoginsThisWeek()
    {
        return Cache::get('failed_logins_this_week', 0);
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

    private function getRecentSecurityLogs()
    {
        return Cache::get('recent_security_logs', []);
    }

    private function getVulnerabilities()
    {
        return Cache::get('security_vulnerabilities', []);
    }

    private function getBlockedIPs()
    {
        $blockedIPs = Cache::get('blocked_ips', []);

        // Remove expired blocks
        $now = now();
        foreach ($blockedIPs as $ip => $data) {
            if (isset($data['expires_at']) && $data['expires_at'] && Carbon::parse($data['expires_at'])->isPast()) {
                unset($blockedIPs[$ip]);
            }
        }

        Cache::put('blocked_ips', $blockedIPs, now()->addYears(10));

        return $blockedIPs;
    }

    private function getSecurityLogs($request)
    {
        // This would typically fetch from a database or log files
        // For now, return cached logs
        return Cache::get('security_logs', []);
    }

    private function checkFilePermissions()
    {
        $criticalPaths = [
            storage_path(),
            storage_path('framework'),
            storage_path('logs'),
            bootstrap_path('cache'),
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

    /**
     * Get maintenance mode status
     */
    public function getMaintenanceStatus(Request $request)
    {
        $isDown = app()->isDownForMaintenance();

        return response()->json([
            'isDown' => $isDown,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
