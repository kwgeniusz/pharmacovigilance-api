<?php

use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-22 12:00:00');
    actingAsApiUser(User::factory()->administrator()->create());
});

afterEach(fn () => Carbon::setTestNow());

test('an administrator can export filtered affected orders as csv', function () {
    $customer = Customer::factory()->create([
        'name' => '=Alice Johnson',
        'email' => 'alice@example.test',
        'phone' => '+1 555 010 1001',
    ]);
    $affected = Medication::factory()->create([
        'name' => '+Compounded Metformin',
        'lot_number' => '951357',
    ]);
    $other = Medication::factory()->create(['lot_number' => '111111']);
    $matchingOrder = Order::factory()->for($customer)->create(['purchase_date' => '2026-08-12']);
    $oldOrder = Order::factory()->for($customer)->create(['purchase_date' => '2026-06-01']);
    $unrelatedOrder = Order::factory()->for($customer)->create(['purchase_date' => '2026-08-10']);

    OrderItem::factory()->for($matchingOrder)->for($affected)->create();
    OrderItem::factory()->for($oldOrder)->for($affected)->create();
    OrderItem::factory()->for($unrelatedOrder)->for($other)->create();

    $response = $this->get(
        '/api/orders/export?lot_number=951357&start_date=2026-08-01&end_date=2026-08-22',
        ['Accept' => 'text/csv'],
    );

    $response
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8')
        ->assertDownload('affected-orders-951357.csv');

    $rows = array_map(
        fn (string $row): array => str_getcsv($row, ',', '"', ''),
        preg_split('/\r\n|\r|\n/', trim($response->streamedContent())),
    );

    expect($rows)->toHaveCount(2)
        ->and($rows[0])->toBe([
            'Order ID',
            'Customer',
            'Email',
            'Phone',
            'Purchase Date',
            'Medication',
            'Lot Number',
        ])
        ->and($rows[1][0])->toBe((string) $matchingOrder->id)
        ->and($rows[1][1])->toBe("'=Alice Johnson")
        ->and($rows[1][5])->toBe("'+Compounded Metformin")
        ->and($rows[1][6])->toBe('951357');
});

test('csv export applies the same date validation as order search', function () {
    $this->getJson(
        '/api/orders/export?lot_number=951357&start_date=2026-08-22&end_date=2026-08-01',
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('end_date');
});
