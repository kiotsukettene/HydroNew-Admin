<?php

namespace Database\Seeders;

use App\Models\TreatmentReport;
use App\Models\TreatmentStage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TreatmentStagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reports = TreatmentReport::all();
        $stageNames = ['MFC', 'Natural Filter', 'UV Filter', 'Clean Water Tank'];
        $statuses = ['pending', 'processing', 'passed', 'failed'];

        foreach ($reports as $report) {
            foreach ($stageNames as $index => $stageName) {
                TreatmentStage::create([
                    'treatment_id' => $report->id,
                    'stage_name' => $stageName,
                    'stage_order' => $index + 1,
                    'status' => $statuses[array_rand($statuses)],
                    'pH' => rand(60, 75) / 10,
                    'turbidity' => rand(0, 50),
                    'TDS' => rand(500, 1500),
                    'notes' => 'Treatment stage ' . ($index + 1),
                ]);
            }
        }
    }
}
