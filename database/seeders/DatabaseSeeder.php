<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => 'password',
            'role' => UserRole::Administrator,
        ]);

        User::factory()->create([
            'username' => 'operator',
            'password' => 'password',
            'role' => UserRole::Operator,
        ]);

        $this->call([
            CustomerSeeder::class,
            MedicationSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
        ]);
    }
}
