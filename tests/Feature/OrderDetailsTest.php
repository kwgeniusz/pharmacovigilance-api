<?php

use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;

beforeEach(fn () => actingAsApiUser());

test('order details include the customer and every medication', function () {
    $order = Order::factory()->create();
    $firstMedication = Medication::factory()->create(['lot_number' => '951357']);
    $secondMedication = Medication::factory()->create(['lot_number' => '111111']);
    OrderItem::factory()->for($order)->for($firstMedication)->create();
    OrderItem::factory()->for($order)->for($secondMedication)->create();

    $this->getJson("/api/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $order->id)
        ->assertJsonPath('data.customer.id', $order->customer_id)
        ->assertJsonCount(2, 'data.items');
});

test('an unknown order returns not found', function () {
    $this->getJson('/api/orders/999999')->assertNotFound();
});
