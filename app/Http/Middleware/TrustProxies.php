<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * Get the trusted headers.
     *
     * @return int
     */
    protected function headers()
    {
        // Only trust the protocol header in non-local environments
        if (app()->environment('local')) {
            return Request::HEADER_X_FORWARDED_FOR |
                   Request::HEADER_X_FORWARDED_HOST |
                   Request::HEADER_X_FORWARDED_PORT |
                   Request::HEADER_X_FORWARDED_AWS_ELB;
        } else {
            return Request::HEADER_X_FORWARDED_FOR |
                   Request::HEADER_X_FORWARDED_HOST |
                   Request::HEADER_X_FORWARDED_PORT |
                   Request::HEADER_X_FORWARDED_PROTO |
                   Request::HEADER_X_FORWARDED_AWS_ELB;
        }
    }
}