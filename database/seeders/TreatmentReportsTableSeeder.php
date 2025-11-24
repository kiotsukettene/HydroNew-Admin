<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\TreatmentReport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TreatmentReportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devices = Device::all();
        $statuses = ['pending', 'success', 'failed'];

        foreach ($devices as $device) {
            for ($i = 0; $i < 3; $i++) {
                TreatmentReport::create([
                    'device_id' => $device->id,
                    'start_time' => now()->subDays($i)->subHours(rand(1, 24)),
                    'end_time' => now()->subDays($i),
                    'final_status' => $statuses[array_rand($statuses)],
                    'total_cycles' => rand(1, 10),
                ]);
            }
        }
    }
}
