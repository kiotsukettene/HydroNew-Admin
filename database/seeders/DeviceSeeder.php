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
        $users = User::where('role', 'user')->get();
        
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

        $statuses = ['online', 'offline'];
        $models = ['Model A', 'Model B', 'Model C', 'Model X'];
        $firmwareVersions = ['1.0.0', '1.1.0', '2.0.0', '2.1.5'];

        // Create devices and assign to users via pivot table
        foreach ($users as $index => $user) {
            $deviceType = $deviceTypes[$index % count($deviceTypes)];
            $serialPrefix = strtoupper(substr(str_replace(' ', '', $deviceType), 0, 4));
            
            $device = Device::create([
                'device_name' => $deviceType . ' ' . ($index + 1),
                'serial_number' => sprintf('%s-%s-%05d', $serialPrefix, date('Y'), $index + 1),
                'model' => $models[array_rand($models)],
                'firmware_version' => $firmwareVersions[array_rand($firmwareVersions)],
                'status' => $statuses[array_rand($statuses)],
                'is_archived' => false,
                'created_at' => now()->subDays(rand(0, 90)),
                'updated_at' => now()->subDays(rand(0, 30)),
            ]);

            // Assign device to user using many-to-many relationship
            $device->users()->attach($user->id);
        }

        $this->command->info('Created ' . $users->count() . ' devices successfully!');
    }
}

