<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can register and session is created without token in response', function () {
    $payload = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->withHeaders([
        'Referer' => 'http://localhost:3000',
    ])->postJson('/api/auth/register', $payload);

    $response->assertCreated()
        ->assertJsonPath('user.email', 'john@example.com')
        ->assertJsonMissingPath('token');

    $this->assertAuthenticated();
});

test('user can login and session is created without token in response', function () {
    $user = User::factory()->create([
        'email' => 'jane@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->withHeaders([
        'Referer' => 'http://localhost:3000',
    ])->postJson('/api/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonPath('user.email', 'jane@example.com')
        ->assertJsonMissingPath('token');

    $this->assertAuthenticatedAs($user);
});

test('login fails with invalid credentials', function () {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => bcrypt('password123'),
    ]);

    $this->withHeaders([
        'Referer' => 'http://localhost:3000',
    ])->postJson('/api/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'wrongpassword',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['email']);

    $this->assertGuest();
});

test('authenticated user can get current profile from /auth/me', function () {
    $user = User::factory()->create();

    $this->withHeaders([
        'Referer' => 'http://localhost:3000',
    ])->actingAs($user, 'web')
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('user.email', $user.email);
});

test('unauthenticated user gets 401 on /auth/me', function () {
    $this->withHeaders([
        'Referer' => 'http://localhost:3000',
    ])->getJson('/api/auth/me')
        ->assertUnauthorized();
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    $response = $this->withHeaders([
        'Referer' => 'http://localhost:3000',
    ])->actingAs($user, 'web')
        ->postJson('/api/auth/logout');

    $response->assertOk()
        ->assertJsonPath('message', 'Logged out successfully.');

    $this->assertGuest();
});
