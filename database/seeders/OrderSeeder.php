<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purchaseDates = [
            'alice@example.test' => today()->subDays(10),
            'benjamin@example.test' => today()->subDays(25),
            'carol@example.test' => today()->subDays(45),
            'david@example.test' => today()->subDays(5),
        ];

        foreach ($purchaseDates as $email => $purchaseDate) {
            Order::query()->create([
                'customer_id' => Customer::query()->where('email', $email)->sole()->id,
                'purchase_date' => $purchaseDate,
            ]);
        }
    }
}
