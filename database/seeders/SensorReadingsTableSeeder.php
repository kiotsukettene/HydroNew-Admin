<?php

namespace Database\Seeders;

use App\Models\Sensor;
use App\Models\SensorReading;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SensorReadingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sensors = Sensor::all();

        foreach ($sensors as $sensor) {
            for ($i = 0; $i < 10; $i++) {
                $value = match($sensor->type) {
                    'ph' => rand(60, 75) / 10,
                    'turbidity' => rand(0, 100),
                    'tds' => rand(500, 2000),
                    'temperature' => rand(18, 28),
                    'water_level' => rand(20, 100),
                    'electric_current' => rand(1, 50) / 10,
                    'ec' => rand(1000, 3000),
                    'humidity' => rand(40, 90),
                };

                SensorReading::create([
                    'sensor_id' => $sensor->id,
                    'reading_value' => $value,
                    'reading_time' => now()->subHours($i),
                ]);
            }
        }
    }
}
