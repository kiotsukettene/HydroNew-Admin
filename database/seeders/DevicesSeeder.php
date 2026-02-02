<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\DeviceUser;
use App\Models\User;

class DevicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->limit(10)->get();

        $devices = [
            [
                'device_name' => 'HydroStation Alpha',
                'serial_number' => 'HS-2024-0001',
                'model' => 'HS-Pro-X1',
                'firmware_version' => 'v2.1.0',
                'status' => 'online',
                'is_archived' => false,
                'created_at' => now()->subDays(100),
            ],
            [
                'device_name' => 'HydroStation Beta',
                'serial_number' => 'HS-2024-0002',
                'model' => 'HS-Pro-X1',
                'firmware_version' => 'v2.0.8',
                'status' => 'offline',
                'is_archived' => false,
                'created_at' => now()->subDays(85),
            ],
            [
                'device_name' => 'HydroStation Gamma',
                'serial_number' => 'HS-2024-0003',
                'model' => 'HS-Pro-X2',
                'firmware_version' => 'v2.0.5',
                'status' => 'offline',
                'is_archived' => false,
                'created_at' => now()->subDays(70),
            ],
            [
                'device_name' => 'HydroStation Delta',
                'serial_number' => 'HS-2024-0004',
                'model' => 'HS-Pro-X1',
                'firmware_version' => 'v2.1.0',
                'status' => 'online',
                'is_archived' => false,
                'created_at' => now()->subDays(55),
            ],
            [
                'device_name' => 'HydroStation Epsilon',
                'serial_number' => 'HS-2024-0005',
                'model' => 'HS-Pro-X2',
                'firmware_version' => 'v2.1.0',
                'status' => 'online',
                'is_archived' => false,
                'created_at' => now()->subDays(40),
            ],
            [
                'device_name' => 'HydroStation Zeta',
                'serial_number' => 'HS-2024-0006',
                'model' => 'HS-Basic-1',
                'firmware_version' => 'v1.8.2',
                'status' => 'offline',
                'is_archived' => true,
                'created_at' => now()->subDays(150),
            ],
            [
                'device_name' => 'HydroStation Eta',
                'serial_number' => 'HS-2024-0007',
                'model' => 'HS-Pro-X1',
                'firmware_version' => 'v2.1.0',
                'status' => 'online',
                'is_archived' => false,
                'created_at' => now()->subDays(25),
            ],
            [
                'device_name' => 'HydroStation Theta',
                'serial_number' => 'HS-2024-0008',
                'model' => 'HS-Pro-X2',
                'firmware_version' => 'v2.1.1',
                'status' => 'online',
                'is_archived' => false,
                'created_at' => now()->subDays(10),
            ],
            [
                'device_name' => 'HydroStation Iota',
                'serial_number' => 'HS-2024-0009',
                'model' => 'HS-Basic-1',
                'firmware_version' => 'v1.9.0',
                'status' => 'offline',
                'is_archived' => false,
                'created_at' => now()->subDays(5),
            ],
            [
                'device_name' => 'HydroStation Kappa',
                'serial_number' => 'HS-2024-0010',
                'model' => 'HS-Pro-X1',
                'firmware_version' => 'v2.1.0',
                'status' => 'online',
                'is_archived' => false,
                'created_at' => now()->subDays(2),
            ],
        ];

        foreach ($devices as $index => $deviceData) {
            $device = Device::firstOrCreate(
                ['serial_number' => $deviceData['serial_number']],
                [
                    'device_name' => $deviceData['device_name'],
                    'model' => $deviceData['model'],
                    'firmware_version' => $deviceData['firmware_version'],
                    'status' => $deviceData['status'],
                    'is_archived' => $deviceData['is_archived'],
                    'created_at' => $deviceData['created_at'],
                    'updated_at' => $deviceData['created_at'],
                ]
            );

            // Pair device to user if user exists at this index
            if (isset($users[$index])) {
                DeviceUser::firstOrCreate([
                    'user_id' => $users[$index]->id,
                    'device_id' => $device->id,
                ]);
            }
        }
    }
}
