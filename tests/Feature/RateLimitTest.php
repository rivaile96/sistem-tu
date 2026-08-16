<?php

/**
 * Phase 1.6-B — Rate Limiting Regression Tests
 *
 * Verifies throttle:5,1 middleware is active on:
 *   POST /siswa/login   (student portal)
 *   POST /api/login     (parent API)
 *
 * Also verifies unprotected routes are unaffected.
 */

use Illuminate\Support\Facades\RateLimiter;

// Reset rate limiter between each test so counts don't bleed across tests.
beforeEach(function () {
    RateLimiter::clear('siswa-login');
    // Laravel throttle middleware keys by IP — clear all limiter keys
    // by flushing the array cache (phpunit.xml: CACHE_STORE=array, resets per test anyway).
});

// ── Student Login ───────────────────────────────────────────────────────────

test('GET /siswa/login is not rate limited', function () {
    // 10 rapid GET requests must all succeed — throttle only covers POST.
    for ($i = 0; $i < 10; $i++) {
        $response = $this->get('/siswa/login');
        // 200 (form) or 302 (already auth) — either way NOT 429.
        expect($response->status())->not->toBe(429);
    }
});

test('first 5 POST /siswa/login attempts are not rate limited', function () {
    for ($i = 1; $i <= 5; $i++) {
        $response = $this->post('/siswa/login', [
            'nis'      => '0000000000',
            'password' => '010101',
        ]);
        // Any response except 429 is acceptable — auth will fail (no such student)
        // but the throttle must not have fired yet.
        expect($response->status())->not->toBe(429,
            "Request #{$i} should not be throttled yet."
        );
    }
});

test('6th POST /siswa/login attempt returns HTTP 429', function () {
    // Exhaust the 5-attempt limit.
    for ($i = 0; $i < 5; $i++) {
        $this->post('/siswa/login', [
            'nis'      => '0000000000',
            'password' => '010101',
        ]);
    }

    // 6th attempt must be throttled.
    $response = $this->post('/siswa/login', [
        'nis'      => '0000000000',
        'password' => '010101',
    ]);

    $response->assertStatus(429);
});

test('POST /siswa/login 429 response includes Retry-After header', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post('/siswa/login', ['nis' => '0000000000', 'password' => '010101']);
    }

    $response = $this->post('/siswa/login', ['nis' => '0000000000', 'password' => '010101']);

    $response->assertStatus(429);
    expect($response->headers->has('Retry-After'))->toBeTrue();
});

// ── API Login ───────────────────────────────────────────────────────────────

test('first 5 POST /api/login attempts are not rate limited', function () {
    for ($i = 1; $i <= 5; $i++) {
        $response = $this->postJson('/api/login', [
            'nis'   => '0000000000',
            'phone' => '08000000000',
        ]);
        expect($response->status())->not->toBe(429,
            "API request #{$i} should not be throttled yet."
        );
    }
});

test('6th POST /api/login attempt returns HTTP 429', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/login', [
            'nis'   => '0000000000',
            'phone' => '08000000000',
        ]);
    }

    $response = $this->postJson('/api/login', [
        'nis'   => '0000000000',
        'phone' => '08000000000',
    ]);

    $response->assertStatus(429);
});

test('POST /api/login 429 response includes Retry-After header', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/login', ['nis' => '0000000000', 'phone' => '08000000000']);
    }

    $response = $this->postJson('/api/login', ['nis' => '0000000000', 'phone' => '08000000000']);

    $response->assertStatus(429);
    expect($response->headers->has('Retry-After'))->toBeTrue();
});

// ── Unrelated route is unaffected ───────────────────────────────────────────

test('GET /siswa/login is unaffected after POST limit is hit', function () {
    // Exhaust POST limit.
    for ($i = 0; $i < 6; $i++) {
        $this->post('/siswa/login', ['nis' => '0000000000', 'password' => '010101']);
    }

    // GET must still work — different method, different throttle key.
    $response = $this->get('/siswa/login');
    expect($response->status())->not->toBe(429);
});
