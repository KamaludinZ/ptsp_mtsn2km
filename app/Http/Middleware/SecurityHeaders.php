<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
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
        $response = $next($request);

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Strict Transport Security (HSTS)
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy - Environment aware
        $isLocal = config('app.env') === 'local';

        if ($isLocal) {
            // Development: Allow Vite dev server + Filament resources
            $response->headers->set('Content-Security-Policy',
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 http://127.0.0.1:5173 ws://localhost:5173 ws://127.0.0.1:5173; " .
                "style-src 'self' 'unsafe-inline' http://localhost:5173 http://127.0.0.1:5173 https://fonts.bunny.net; " .
                "font-src 'self' data: http://localhost:5173 http://127.0.0.1:5173 https://fonts.bunny.net https://fonts.gstatic.com; " .
                "img-src 'self' data: blob: https://ui-avatars.com; " .
                "connect-src 'self' http://localhost:5173 http://127.0.0.1:5173 ws://localhost:5173 ws://127.0.0.1:5173; " .
                "worker-src 'self' blob:; " .
                "child-src 'self' blob:; " .
                "object-src 'none'; " .
                "base-uri 'self'; " .
                "form-action 'self';"
            );
        } else {
            // Production: Allow Filament resources
            $response->headers->set('Content-Security-Policy',
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
                "style-src 'self' 'unsafe-inline' https://fonts.bunny.net; " .
                "font-src 'self' data: https://fonts.bunny.net https://fonts.gstatic.com; " .
                "img-src 'self' data: blob: https://ui-avatars.com; " .
                "connect-src 'self'; " .
                "worker-src 'self' blob:; " .
                "child-src 'self' blob:; " .
                "object-src 'none'; " .
                "base-uri 'self'; " .
                "form-action 'self';"
            );
        }

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (formerly Feature Policy)
        $response->headers->set('Permissions-Policy',
            'geolocation=(), microphone=(), camera=()'
        );

        return $response;
    }
}
