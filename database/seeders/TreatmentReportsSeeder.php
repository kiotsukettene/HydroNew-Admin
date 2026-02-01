<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\TreatmentReport;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TreatmentReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devices = Device::where('is_archived', false)->limit(5)->get();

        if ($devices->isEmpty()) {
            return;
        }

        // Create treatment reports distributed across devices and time
        $reports = [
            // Device 1 - Recent treatments
            ['device_index' => 0, 'days_ago' => 2, 'duration_hours' => 3, 'status' => 'success', 'cycles' => 5],
            ['device_index' => 0, 'days_ago' => 15, 'duration_hours' => 4, 'status' => 'success', 'cycles' => 6],
            ['device_index' => 0, 'days_ago' => 30, 'duration_hours' => 2, 'status' => 'failed', 'cycles' => 3],
            ['device_index' => 0, 'days_ago' => 60, 'duration_hours' => 3, 'status' => 'success', 'cycles' => 4],
            
            // Device 2 - Mixed results
            ['device_index' => 1, 'days_ago' => 5, 'duration_hours' => 5, 'status' => 'success', 'cycles' => 7],
            ['device_index' => 1, 'days_ago' => 20, 'duration_hours' => 3, 'status' => 'success', 'cycles' => 5],
            ['device_index' => 1, 'days_ago' => 45, 'duration_hours' => 4, 'status' => 'pending', 'cycles' => 4],
            ['device_index' => 1, 'days_ago' => 75, 'duration_hours' => 2, 'status' => 'failed', 'cycles' => 2],
            
            // Device 3 - High frequency
            ['device_index' => 2, 'days_ago' => 1, 'duration_hours' => 2, 'status' => 'success', 'cycles' => 4],
            ['device_index' => 2, 'days_ago' => 7, 'duration_hours' => 3, 'status' => 'success', 'cycles' => 5],
            ['device_index' => 2, 'days_ago' => 14, 'duration_hours' => 2, 'status' => 'success', 'cycles' => 3],
            ['device_index' => 2, 'days_ago' => 21, 'duration_hours' => 4, 'status' => 'success', 'cycles' => 6],
            ['device_index' => 2, 'days_ago' => 35, 'duration_hours' => 3, 'status' => 'success', 'cycles' => 5],
            ['device_index' => 2, 'days_ago' => 50, 'duration_hours' => 2, 'status' => 'failed', 'cycles' => 2],
            
            // Device 4 - Moderate frequency
            ['device_index' => 3, 'days_ago' => 10, 'duration_hours' => 4, 'status' => 'success', 'cycles' => 6],
            ['device_index' => 3, 'days_ago' => 25, 'duration_hours' => 3, 'status' => 'success', 'cycles' => 4],
            ['device_index' => 3, 'days_ago' => 55, 'duration_hours' => 5, 'status' => 'success', 'cycles' => 7],
            ['device_index' => 3, 'days_ago' => 90, 'duration_hours' => 2, 'status' => 'pending', 'cycles' => 3],
            
            // Device 5 - Low frequency but consistent
            ['device_index' => 4, 'days_ago' => 12, 'duration_hours' => 3, 'status' => 'success', 'cycles' => 5],
            ['device_index' => 4, 'days_ago' => 40, 'duration_hours' => 4, 'status' => 'success', 'cycles' => 6],
            ['device_index' => 4, 'days_ago' => 80, 'duration_hours' => 3, 'status' => 'success', 'cycles' => 4],
        ];

        foreach ($reports as $report) {
            $deviceIndex = $report['device_index'];
            $startTime = Carbon::now()->subDays($report['days_ago']);
            $endTime = $report['status'] === 'pending' ? null : $startTime->copy()->addHours($report['duration_hours']);

            TreatmentReport::create([
                'device_id' => $devices[$deviceIndex]->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'final_status' => $report['status'],
                'total_cycles' => $report['cycles'],
            ]);
        }
    }
}
