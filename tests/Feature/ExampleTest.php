<?php

// Homepage redirect ke login untuk user yang belum auth — ini behavior yang benar.
// Test default Laravel boilerplate di-update agar sesuai dengan app ini.
it('returns a redirect to login for unauthenticated users', function () {
    $response = $this->get('/');

    $response->assertStatus(302)
             ->assertRedirect('/login');
});
