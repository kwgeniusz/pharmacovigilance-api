<?php

use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-21 12:00:00');
    actingAsApiUser();
});

afterEach(fn () => Carbon::setTestNow());

test('the default search returns affected orders from the last thirty days', function () {
    $customer = Customer::factory()->create();
    $affected = Medication::factory()->create(['lot_number' => '951357']);
    $other = Medication::factory()->create(['lot_number' => '111111']);

    $recentAffectedOrder = Order::factory()->for($customer)->create(['purchase_date' => today()->subDays(10)]);
    $oldAffectedOrder = Order::factory()->for($customer)->create(['purchase_date' => today()->subDays(31)]);
    $unrelatedOrder = Order::factory()->for($customer)->create(['purchase_date' => today()->subDays(5)]);

    OrderItem::factory()->for($recentAffectedOrder)->for($affected)->create();
    OrderItem::factory()->for($recentAffectedOrder)->for($other)->create();
    OrderItem::factory()->for($oldAffectedOrder)->for($affected)->create();
    OrderItem::factory()->for($unrelatedOrder)->for($other)->create();

    $this->getJson('/api/orders?lot_number=951357')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $recentAffectedOrder->id)
        ->assertJsonPath('data.0.customer.email', $customer->email)
        ->assertJsonCount(1, 'data.0.items')
        ->assertJsonPath('data.0.items.0.medication.lot_number', '951357');
});

test('explicit date boundaries are inclusive', function () {
    $medication = Medication::factory()->create(['lot_number' => '951357']);
    $first = Order::factory()->create(['purchase_date' => '2026-08-01']);
    $last = Order::factory()->create(['purchase_date' => '2026-08-15']);
    OrderItem::factory()->for($first)->for($medication)->create();
    OrderItem::factory()->for($last)->for($medication)->create();

    $this->getJson('/api/orders?lot_number=951357&start_date=2026-08-01&end_date=2026-08-15')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a reversed date range is rejected', function () {
    $this->getJson('/api/orders?lot_number=951357&start_date=2026-08-15&end_date=2026-08-01')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('end_date');
});

test('order search results are paginated', function () {
    $customer = Customer::factory()->create();
    $medication = Medication::factory()->create(['lot_number' => '951357']);

    Order::factory()
        ->count(16)
        ->for($customer)
        ->create(['purchase_date' => today()])
        ->each(fn (Order $order) => OrderItem::factory()->for($order)->for($medication)->create());

    $this->getJson('/api/orders?lot_number=951357')
        ->assertOk()
        ->assertJsonCount(15, 'data')
        ->assertJsonPath('meta.total', 16)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonStructure(['links', 'meta']);
});
