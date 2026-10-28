<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Behind a reverse proxy (Coolify/Traefik, Docker) trust its
        // X-Forwarded-* headers so HTTPS URLs, secure cookies and client IPs
        // are right. Comma-separated list of proxy IPs/CIDR ranges, or "*"
        // when the app is reachable only through the proxy (Docker image);
        // "*" never takes the client IP from X-Forwarded-For (ProxyTrust).
        if ($proxies = env('TRUSTED_PROXIES')) {
            $proxies === '*'
                ? $middleware->trustProxies(at: '*', headers: \App\Support\ProxyTrust::WILDCARD_HEADERS)
                : $middleware->trustProxies(at: array_map('trim', explode(',', $proxies)));
        }

        // Global middleware
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\CheckBlockedIP::class);
        $middleware->append(\App\Http\Middleware\LogSuspiciousActivity::class);
        $middleware->web(append: [\App\Http\Middleware\TrackSiteVisit::class, \App\Http\Middleware\GuardImpersonation::class]);

        // Middleware aliases
        $middleware->alias([
            'check.blocked.ip' => \App\Http\Middleware\CheckBlockedIP::class,
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
            'log.suspicious' => \App\Http\Middleware\LogSuspiciousActivity::class,
            'user.type' => \App\Http\Middleware\UserTypeMiddleware::class,
            'check.email.verification' => \App\Http\Middleware\CheckEmailVerification::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            // Per-route permission check that works for the API (sanctum) too: izin:backoffice.access|peran:admin
            'izin' => \App\Http\Middleware\RequirePermission::class,
            // Konteks peran aktif petugas (ActiveRoles::inContext) di luar panel /cp
            'peran.aktif' => \App\Http\Middleware\SetActiveRoleContext::class,
            // Izin per route menurut peran aktif: izin.aktif:backoffice.access,backoffice.sk.register
            'izin.aktif' => \App\Http\Middleware\RequireActivePermission::class,
            // Kemampuan token API (Sanctum): ability = salah satu, abilities = semuanya
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // A token issued for less (AuthController::TOKEN_ABILITIES) than the endpoint asks;
        // Laravel turns the authorization error into a 403 first, the cause stays as "previous".
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            $missing = $e->getPrevious();

            return $missing instanceof \Laravel\Sanctum\Exceptions\MissingAbilityException ? response()->json([
                'message' => 'Token ini tidak memiliki kemampuan untuk aksi ini. Masuk ulang dan minta kemampuan yang sesuai.',
                'kemampuan_dibutuhkan' => array_values($missing->abilities()),
            ], 403) : null;
        });
    })->create();