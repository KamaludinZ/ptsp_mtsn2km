<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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
        $viteUrl = $this->getViteDevServerUrl();

        if ($isLocal && $viteUrl) {
            // Development: Allow Vite dev server + Filament resources
            $response->headers->set('Content-Security-Policy',
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' {$viteUrl} ws:" . str_replace(['http:', 'https:'], '', $viteUrl) . "; " .
                "style-src 'self' 'unsafe-inline' {$viteUrl} https://fonts.bunny.net; " .
                "font-src 'self' data: {$viteUrl} https://fonts.bunny.net https://fonts.gstatic.com; " .
                "img-src 'self' data: blob: https://ui-avatars.com; " .
                "connect-src 'self' {$viteUrl} ws:" . str_replace(['http:', 'https:'], '', $viteUrl) . "; " .
                "worker-src 'self' blob:; " .
                "child-src 'self' blob:; " .
                "object-src 'none'; " .
                "base-uri 'self'; " .
                "form-action 'self';"
            );
        } else {
            // Production or Vite not running: Allow Filament resources
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

    /**
     * Get the Vite development server URL from the hot file.
     *
     * @return string|null
     */
    private function getViteDevServerUrl(): ?string
    {
        $hotFilePath = public_path('hot');

        if (File::exists($hotFilePath)) {
            return rtrim(File::get($hotFilePath));
        }

        return null;
    }
}