<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\Sensor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SensorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devices = Device::all();
        $sensorTypes = ['ph', 'turbidity', 'tds', 'temperature', 'water_level', 'electric_current', 'ec', 'humidity'];
        $units = ['pH', '%', 'mg/L', '°C', 'cm', 'A', 'µS/cm', '%'];

        foreach ($devices as $device) {
            foreach ($sensorTypes as $index => $type) {
                Sensor::create([
                    'device_id' => $device->id,
                    'type' => $type,
                    'unit' => $units[$index],
                ]);
            }
        }
    }
}
