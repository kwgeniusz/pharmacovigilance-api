<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Medication;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $affectedMedication = Medication::query()->where('lot_number', '951357')->sole();
        $metformin = Medication::query()->where('lot_number', '842610')->sole();
        $lisinopril = Medication::query()->where('lot_number', '775204')->sole();

        $orders = Customer::query()
            ->with('orders')
            ->get()
            ->mapWithKeys(fn (Customer $customer) => [$customer->email => $customer->orders->sole()]);

        OrderItem::query()->insert([
            [
                'order_id' => $orders['alice@example.test']->id,
                'medication_id' => $affectedMedication->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $orders['alice@example.test']->id,
                'medication_id' => $metformin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $orders['benjamin@example.test']->id,
                'medication_id' => $affectedMedication->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $orders['carol@example.test']->id,
                'medication_id' => $affectedMedication->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $orders['david@example.test']->id,
                'medication_id' => $lisinopril->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
