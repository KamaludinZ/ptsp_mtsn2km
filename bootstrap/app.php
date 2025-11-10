<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\CheckBlockedIP::class);
        $middleware->append(\App\Http\Middleware\LogSuspiciousActivity::class);

        // Middleware aliases
        $middleware->alias([
            'check.blocked.ip' => \App\Http\Middleware\CheckBlockedIP::class,
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
            'log.suspicious' => \App\Http\Middleware\LogSuspiciousActivity::class,
            'user.type' => \App\Http\Middleware\UserTypeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();