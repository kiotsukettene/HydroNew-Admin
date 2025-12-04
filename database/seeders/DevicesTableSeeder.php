<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DevicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            Device::create([
                'user_id' => $user->id,
                'name' => 'Device ' . $user->id,
                'serial_number' => 'SN-' . uniqid(),
                'status' => 'connected',
            ]);

            Device::create([
                'user_id' => $user->id,
                'name' => 'Device ' . ($user->id + 100),
                'serial_number' => 'SN-' . uniqid(),
                'status' => 'not connected',
            ]);
        }
    }
}
