<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_sends_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('X-Download-Options', 'noopen');
        $response->assertHeader('Permissions-Policy');
        $response->assertOk();
    }

    public function test_https_request_sends_hsts(): void
    {
        $response = $this->get('/', ['X-Forwarded-Proto' => 'https']);

        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_plain_http_request_does_not_send_hsts(): void
    {
        $response = $this->get('http://localhost/');

        $response->assertHeaderMissing('Strict-Transport-Security');
    }
}