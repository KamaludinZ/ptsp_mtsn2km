<?php

namespace Tests\Unit;

use App\Support\ProxyTrust;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

/** TRUSTED_PROXIES=*: HTTPS comes from the proxy, the client IP cannot be faked. */
class ProxyTrustTest extends TestCase
{
    protected function tearDown(): void
    {
        TrustProxies::flushState();
        Request::setTrustedProxies([], -1);
        parent::tearDown();
    }

    public function test_a_forged_forwarded_for_header_does_not_change_the_client_ip(): void
    {
        TrustProxies::at('*');
        TrustProxies::withHeaders(ProxyTrust::WILDCARD_HEADERS);

        // nginx already put the real client in REMOTE_ADDR; the visitor wrote X-Forwarded-For itself.
        $request = Request::create('http://ptsp.test/', 'GET', server: [
            'REMOTE_ADDR' => '203.0.113.9',
            'HTTP_X_FORWARDED_FOR' => '1.2.3.4, 203.0.113.9',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);
        (new TrustProxies())->handle($request, fn () => null);

        $this->assertSame('203.0.113.9', $request->ip());
        $this->assertTrue($request->isSecure());
    }
}
