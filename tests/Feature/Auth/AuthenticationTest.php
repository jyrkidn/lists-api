<?php

use App\Models\User;

beforeEach(fn () => $this->user = User::factory()->create());

test('users can authenticate using the login api', function () {
    $response = $this->post('/login', [
        'email' => $this->user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertNoContent();
});

test('users can not authenticate with invalid password', function () {
    $this->post('/login', [
        'email' => $this->user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});
