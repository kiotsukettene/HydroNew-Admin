<?php

namespace Database\Seeders;

use App\Models\HydroponicSetup;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HydroponicSetupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $crops = ['Lettuce', 'Tomato', 'Cucumber', 'Basil', 'Spinach', 'Kale', 'Peppers', 'Strawberry'];
        $bedSizes = ['small', 'medium', 'large'];

        foreach ($users as $user) {
            for ($i = 0; $i < 2; $i++) {
                HydroponicSetup::create([
                    'user_id' => $user->id,
                    'crop_name' => $crops[array_rand($crops)],
                    'number_of_crops' => rand(10, 100),
                    'bed_size' => $bedSizes[array_rand($bedSizes)],
                    'pump_config' => json_encode(['flow_rate' => rand(10, 50), 'frequency' => rand(1, 5)]),
                    'nutrient_solution' => 'Solution Type ' . ($i + 1),
                    'target_ph_min' => 6.0,
                    'target_ph_max' => 6.8,
                    'target_tds_min' => 1000,
                    'target_tds_max' => 1500,
                    'water_amount' => rand(50, 200) . 'L',
                    'setup_date' => now()->subDays(rand(1, 30)),
                    'status' => 'active',
                ]);
            }
        }
    }
}
