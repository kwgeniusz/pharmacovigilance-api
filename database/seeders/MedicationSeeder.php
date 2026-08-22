<?php

namespace Database\Seeders;

use App\Models\Medication;
use Illuminate\Database\Seeder;

class MedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Medication::query()->insert([
            [
                'name' => 'Compounded Amoxicillin Suspension',
                'lot_number' => '951357',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Metformin Extended Release',
                'lot_number' => '842610',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lisinopril Tablets',
                'lot_number' => '775204',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
