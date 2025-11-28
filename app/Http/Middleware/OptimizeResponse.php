<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptimizeResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add caching headers for static assets and pages
        if ($this->shouldCache($request)) {
            $response->header('Cache-Control', 'public, max-age=31536000, immutable');
            $response->header('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        } elseif ($request->is('api/*')) {
            // API responses: no cache
            $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', '0');
        } else {
            // Regular pages: no cache
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        // Add compression headers
        if ($this->shouldCompress($request) && !$response->headers->has('Content-Encoding')) {
            $response->header('Vary', 'Accept-Encoding');
        }

        // Add security headers for performance
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'SAMEORIGIN');
        $response->header('X-XSS-Protection', '1; mode=block');

        // Preload critical resources
        if ($response->headers->get('Content-Type') === 'text/html' ||
            str_contains($response->headers->get('Content-Type') ?? '', 'text/html')) {
            $preloadLinks = $this->getPreloadLinks();
            if (!empty($preloadLinks)) {
                $response->header('Link', implode(', ', $preloadLinks));
            }
        }

        return $response;
    }

    /**
     * Determine if the request should be cached.
     */
    private function shouldCache(Request $request): bool
    {
        // Cache build assets permanently
        if ($request->is('build/*')) {
            return true;
        }

        // Cache images, fonts, and other static assets
        $staticExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'otf'];
        $path = $request->path();
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return in_array(strtolower($extension), $staticExtensions);
    }

    /**
     * Determine if the response should be compressed.
     */
    private function shouldCompress(Request $request): bool
    {
        $acceptEncoding = $request->header('Accept-Encoding', '');

        return str_contains($acceptEncoding, 'gzip') ||
               str_contains($acceptEncoding, 'deflate') ||
               str_contains($acceptEncoding, 'br');
    }

    /**
     * Get preload links for critical resources.
     */
    private function getPreloadLinks(): array
    {
        $links = [];

        // You can add specific critical resources here
        // Example: $links[] = '</build/assets/app.js>; rel=preload; as=script';

        return $links;
    }
}
