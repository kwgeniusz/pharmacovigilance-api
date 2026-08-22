<?php

use App\Enums\UserRole;
use App\Models\Medication;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-21 12:00:00');
});

afterEach(fn () => Carbon::setTestNow());

test('the deterministic seeder creates the administrator and required order scenarios', function () {
    $this->seed(DatabaseSeeder::class);

    $administrator = User::query()->where('username', 'admin')->sole();
    $operator = User::query()->where('username', 'operator')->sole();
    $affectedMedication = Medication::query()->where('lot_number', '951357')->sole();

    expect(Hash::check('password', $administrator->password))->toBeTrue()
        ->and($administrator->role)->toBe(UserRole::Administrator)
        ->and(Hash::check('password', $operator->password))->toBeTrue()
        ->and($operator->role)->toBe(UserRole::Operator)
        ->and($affectedMedication->orders()->whereDate('purchase_date', today()->subDays(10))->exists())->toBeTrue()
        ->and($affectedMedication->orders()->whereDate('purchase_date', today()->subDays(25))->exists())->toBeTrue()
        ->and($affectedMedication->orders()->whereDate('purchase_date', today()->subDays(45))->exists())->toBeTrue()
        ->and(Order::query()->whereDate('purchase_date', today()->subDays(5))->whereDoesntHave(
            'medications',
            fn ($query) => $query->where('lot_number', '951357'),
        )->exists())->toBeTrue();
});

test('orders expose their customer and medication relationships', function () {
    $this->seed(DatabaseSeeder::class);

    $order = Order::query()
        ->whereHas('medications', fn ($query) => $query->where('lot_number', '951357'))
        ->with(['customer', 'items.medication', 'medications'])
        ->firstOrFail();

    expect($order->customer->email)->toEndWith('@example.test')
        ->and($order->items)->not->toBeEmpty()
        ->and($order->medications->pluck('lot_number'))->toContain('951357');
});
