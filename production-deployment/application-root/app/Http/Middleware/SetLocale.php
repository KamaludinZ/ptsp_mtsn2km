<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            $supportedLocales = config('app.supported_locales', ['en', 'id']);
            
            if (in_array($locale, $supportedLocales)) {
                App::setLocale($locale);
            }
        } else {
            // Set default locale if none is set in session
            $defaultLocale = config('app.locale', 'en');
            App::setLocale($defaultLocale);
        }

        return $next($request);
    }
}