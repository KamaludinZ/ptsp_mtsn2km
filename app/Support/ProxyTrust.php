<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Which X-Forwarded-* headers to believe when TRUSTED_PROXIES is "*".
 * "*" trusts whoever connects, so X-Forwarded-For (which any visitor can
 * write) is left out: the client IP stays the connection's address, which
 * the Docker image's nginx already resolves from the proxy (docker/nginx.conf).
 * HTTPS, host and port still come from the proxy.
 */
final class ProxyTrust
{
    public const WILDCARD_HEADERS = Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_PREFIX;
}
