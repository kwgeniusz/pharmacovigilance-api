<?php

use App\Mail\MedicationRecallAlert;
use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    actingAsApiUser();
    Mail::fake();
});

test('an alert is sent to the customer for an affected order', function () {
    $order = Order::factory()->create();
    $medication = Medication::factory()->create([
        'name' => 'Compounded Amoxicillin Suspension',
        'lot_number' => '951357',
    ]);
    OrderItem::factory()->for($order)->for($medication)->create();

    $this->postJson('/api/alerts/send', [
        'order_id' => $order->id,
        'lot_number' => '951357',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Alert sent successfully.');

    Mail::assertSent(
        MedicationRecallAlert::class,
        fn (MedicationRecallAlert $mail) => $mail->hasTo($order->customer->email)
            && $mail->lot === '951357'
            && $mail->medications->pluck('id')->contains($medication->id),
    );
});

test('an alert is rejected when the order does not contain the lot', function () {
    $order = Order::factory()->create();
    $medication = Medication::factory()->create(['lot_number' => '111111']);
    OrderItem::factory()->for($order)->for($medication)->create();

    $this->postJson('/api/alerts/send', [
        'order_id' => $order->id,
        'lot_number' => '951357',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('lot_number');

    Mail::assertNothingSent();
});
