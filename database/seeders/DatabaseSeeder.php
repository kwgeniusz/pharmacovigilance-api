<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $this->call([
            CustomerSeeder::class,
            MedicationSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
        ]);
    }
}
