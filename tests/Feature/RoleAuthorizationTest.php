<?php

use App\Enums\UserRole;
use App\Models\User;

test('the authenticated user resource includes the assigned role', function () {
    $administrator = User::factory()->administrator()->create();
    actingAsApiUser($administrator);

    $this->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('data.role', UserRole::Administrator->value);
});

test('an operator can use pharmacovigilance searches', function () {
    $operator = User::factory()->create(['role' => UserRole::Operator]);
    actingAsApiUser($operator);

    $this->getJson('/api/orders?lot_number=951357')
        ->assertOk();
});

test('an operator cannot export affected orders', function () {
    $operator = User::factory()->create(['role' => UserRole::Operator]);
    actingAsApiUser($operator);

    $this->getJson('/api/orders/export?lot_number=951357')
        ->assertForbidden()
        ->assertJsonPath('message', 'This action is unauthorized.');
});
