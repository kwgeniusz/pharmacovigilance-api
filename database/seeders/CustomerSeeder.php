<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::query()->insert([
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@example.test',
                'phone' => '+1 555 010 1001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Benjamin Carter',
                'email' => 'benjamin@example.test',
                'phone' => '+1 555 010 1002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Carol Martinez',
                'email' => 'carol@example.test',
                'phone' => '+1 555 010 1003',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'David Wilson',
                'email' => 'david@example.test',
                'phone' => '+1 555 010 1004',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
