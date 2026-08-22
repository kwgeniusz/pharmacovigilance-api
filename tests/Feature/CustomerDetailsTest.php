<?php

use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;

beforeEach(fn () => actingAsApiUser());

test('customer details include contact information and order history', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create();
    $medication = Medication::factory()->create();
    OrderItem::factory()->for($order)->for($medication)->create();

    $this->getJson("/api/customers/{$customer->id}")
        ->assertOk()
        ->assertJsonPath('data.email', $customer->email)
        ->assertJsonPath('data.phone', $customer->phone)
        ->assertJsonPath('data.orders.0.id', $order->id)
        ->assertJsonPath('data.orders.0.items.0.medication.id', $medication->id);
});

test('an unknown customer returns not found', function () {
    $this->getJson('/api/customers/999999')->assertNotFound();
});
