<?php

use App\Enums\UserRole;
use App\Models\User;

beforeEach(function (): void {
    config()->set('sanctum.stateful', ['localhost:5173']);
    $this->withHeader('Origin', 'http://localhost:5173');
});

test('a user can log in with a username and password', function () {
    $user = User::factory()->create([
        'username' => 'pharmacist',
        'password' => 'secret-password',
        'role' => UserRole::Operator,
    ]);

    $response = $this->postJson('/api/login', [
        'username' => 'pharmacist',
        'password' => 'secret-password',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.username', 'pharmacist')
        ->assertJsonPath('data.role', 'operator');
    $this->assertAuthenticatedAs($user);
});

test('invalid credentials return a validation error', function () {
    User::factory()->create([
        'username' => 'pharmacist',
        'password' => 'secret-password',
    ]);

    $this->postJson('/api/login', [
        'username' => 'pharmacist',
        'password' => 'incorrect-password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('username');
});

test('an authenticated user can retrieve their profile and log out', function () {
    $user = User::factory()->create([
        'username' => 'pharmacist',
        'password' => 'secret-password',
    ]);

    $this->postJson('/api/login', [
        'username' => 'pharmacist',
        'password' => 'secret-password',
    ])->assertOk();

    $this->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);

    $this->postJson('/api/logout')->assertNoContent();
    $this->getJson('/api/user')->assertUnauthorized();
});

test('pharmacovigilance routes reject unauthenticated requests', function () {
    $this->getJson('/api/orders?lot_number=951357')->assertUnauthorized();
});
