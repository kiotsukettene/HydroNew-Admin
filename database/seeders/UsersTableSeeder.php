<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'hydronew@gmail.com',
            'password' => Hash::make('hydronew'),
            'email_verified_at' => now(),
            'first_time_login' => false,
            'role' => 'admin',
            'status' => 'active',
        ]);

        User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@hydroponics.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'first_time_login' => false,
            'role' => 'user',
            'status' => 'active',
        ]);

        User::factory()->count(8)->create();
    }
}
