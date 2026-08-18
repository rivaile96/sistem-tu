<?php

/**
 * Phase 9.5 — Replaced Laravel boilerplate RegistrationTest.
 *
 * Self-registration via /register is intentionally disabled in this application.
 * User accounts are created by administrators only.
 * These tests verify that the registration routes are correctly blocked.
 */

test('registration screen is disabled (returns 404)', function () {
    $this->get('/register')->assertStatus(404);
});

test('registration POST is disabled (returns 404)', function () {
    $this->post('/register', [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ])->assertStatus(404);
});
