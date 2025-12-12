<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users to assign devices to them
        $users = User::where('roles', 'user')->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $deviceTypes = [
            'pH Sensor',
            'TDS Sensor',
            'Temperature Sensor',
            'Humidity Sensor',
            'Water Pump',
            'Light Controller',
            'Nutrient Dispenser',
            'Air Pump',
            'Water Level Sensor',
            'EC Meter',
        ];

        $statuses = ['connected', 'not connected'];

        // Create 31 devices (one per user)
        foreach ($users as $index => $user) {
            $deviceType = $deviceTypes[$index % count($deviceTypes)];
            $serialPrefix = strtoupper(substr(str_replace(' ', '', $deviceType), 0, 4));
            
            Device::create([
                'user_id' => $user->id,
                'name' => $deviceType . ' ' . ($index + 1),
                'serial_number' => sprintf('%s-%s-%05d', $serialPrefix, date('Y'), $index + 1),
                'status' => $statuses[array_rand($statuses)],
                'created_at' => now()->subDays(rand(0, 90)),
                'updated_at' => now()->subDays(rand(0, 30)),
            ]);
        }

        $this->command->info('Created ' . $users->count() . ' devices successfully!');
    }
}

