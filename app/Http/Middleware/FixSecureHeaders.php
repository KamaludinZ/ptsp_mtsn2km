<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FixSecureHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply this fix in local development
        if (app()->environment('local')) {
            // Remove all forwarded protocol headers in local development
            // to prevent Laravel from incorrectly detecting the request as secure
            $request->headers->remove('X-Forwarded-Proto');
            $request->headers->remove('X-Forwarded-Ssl');
            $request->headers->remove('X-Forwarded-Port');

            // Ensure request is treated as non-secure in local
            $request->server->set('HTTPS', 'off');
        }

        return $next($request);
    }
}
