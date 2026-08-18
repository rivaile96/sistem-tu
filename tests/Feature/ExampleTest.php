<?php

/**
 * Phase 9.5 — Replaced Laravel boilerplate ExampleTest.
 *
 * The application root (/) redirects unauthenticated users to /login.
 * Authenticated admin/TU users are redirected to the dashboard.
 */

use App\Models\User;

test('unauthenticated root redirects to login', function () {
    $this->get('/')->assertRedirect('/login');
});

test('authenticated admin root redirects to dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get('/')->assertRedirect();
});
