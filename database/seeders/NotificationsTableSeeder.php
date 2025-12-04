<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $devices = Device::all();
        $types = ['alert', 'warning', 'info', 'success'];
        $messages = [
            'pH level is out of range',
            'Device connection lost',
            'Nutrient levels low',
            'Sensor reading anomaly detected',
            'System maintenance required',
            'Device reconnected successfully',
        ];

        foreach ($users as $user) {
            foreach ($devices->where('user_id', $user->id)->take(2) as $device) {
                for ($i = 0; $i < 3; $i++) {
                    Notification::create([
                        'user_id' => $user->id,
                        'device_id' => $device->id,
                        'title' => 'Device Alert',
                        'message' => $messages[array_rand($messages)],
                        'type' => $types[array_rand($types)],
                        'is_read' => rand(0, 1) > 0.5,
                    ]);
                }
            }
        }
    }
}
