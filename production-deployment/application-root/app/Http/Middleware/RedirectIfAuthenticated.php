<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\RedirectIfAuthenticated as Middleware;

class RedirectIfAuthenticated extends Middleware
{
    /**
     * Get the paths that should be excluded from redirection.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<int, string>
     */
    protected function redirectTo($request)
    {
        if ($request->expectsJson()) {
            return null;
        }

        return route('dashboard');
    }
}