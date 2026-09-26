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
        // are right. Comma-separated list, or "*" when the app is reachable
        // only through the proxy (set in the Docker image).
        if ($proxies = env('TRUSTED_PROXIES')) {
            $middleware->trustProxies(at: $proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        // Global middleware
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\CheckBlockedIP::class);
        $middleware->append(\App\Http\Middleware\LogSuspiciousActivity::class);
        $middleware->web(append: [\App\Http\Middleware\TrackSiteVisit::class]);

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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();