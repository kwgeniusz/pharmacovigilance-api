<?php

use App\Models\Medication;

beforeEach(fn () => actingAsApiUser());

test('medications can be searched by exact lot number', function () {
    Medication::factory()->create([
        'name' => 'Compounded Amoxicillin Suspension',
        'lot_number' => '951357',
    ]);
    Medication::factory()->create(['lot_number' => '111111']);

    $this->getJson('/api/medications/search?lot_number=951357')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Compounded Amoxicillin Suspension')
        ->assertJsonPath('data.0.lot_number', '951357');
});

test('a lot number is required to search medications', function () {
    $this->getJson('/api/medications/search')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('lot_number');
});
