<?php

namespace Database\Seeders;

use App\Models\TipsSuggestion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipsSuggestionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tips = [
            [
                'title' => 'Maintain Optimal pH Levels',
                'description' => 'Regularly monitor and maintain pH levels between 6.0-6.8 to ensure optimal nutrient uptake by your plants.',
                'category' => 'Water Quality',
            ],
            [
                'title' => 'Change Water Regularly',
                'description' => 'Replace your nutrient solution every 2-3 weeks to prevent nutrient imbalances and reduce disease risk.',
                'category' => 'Water Quality',
            ],
            [
                'title' => 'Monitor Plant Growth Stages',
                'description' => 'Track your plants through seedling, vegetative, flowering, and harvest-ready stages to optimize nutrient requirements.',
                'category' => 'Plant Growth',
            ],
            [
                'title' => 'Provide Adequate Lighting',
                'description' => 'Ensure plants receive 12-16 hours of light daily for optimal growth rates.',
                'category' => 'Plant Growth',
            ],
            [
                'title' => 'Clean System Regularly',
                'description' => 'Clean your pump, filters, and pipes monthly to prevent algae growth and maintain system efficiency.',
                'category' => 'System Maintenance',
            ],
            [
                'title' => 'Check Sensor Calibration',
                'description' => 'Calibrate your sensors monthly to ensure accurate readings of pH, TDS, and other parameters.',
                'category' => 'System Maintenance',
            ],
        ];

        foreach ($tips as $tip) {
            TipsSuggestion::create($tip);
        }
    }
}
