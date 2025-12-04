<?php

namespace Database\Seeders;

use App\Models\HydroponicSetup;
use App\Models\HydroponicYield;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HydroponicYieldsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $setups = HydroponicSetup::all();
        $harvestStatuses = ['not_harvested', 'harvested', 'partial'];
        $growthStages = ['seedling', 'vegetative', 'flowering', 'harvest-ready'];
        $healthStatuses = ['good', 'moderate', 'poor'];

        foreach ($setups as $setup) {
            for ($i = 0; $i < 3; $i++) {
                HydroponicYield::create([
                    'hydroponic_setup_id' => $setup->id,
                    'harvest_status' => $harvestStatuses[array_rand($harvestStatuses)],
                    'growth_stage' => $growthStages[array_rand($growthStages)],
                    'health_status' => $healthStatuses[array_rand($healthStatuses)],
                    'predicted_yield' => rand(50, 500),
                    'actual_yield' => rand(40, 480),
                    'harvest_date' => now()->addDays(rand(1, 60)),
                    'system_generated' => true,
                    'notes' => 'Yield record ' . ($i + 1),
                ]);
            }
        }
    }
}
