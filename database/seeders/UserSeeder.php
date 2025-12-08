<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
             'first_name' => 'Oreo',
            'last_name' => 'Cheesecake',
            'email' => 'admin@momo.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'), // change this after first login
            'profile_picture' => null,
            'address' => 'Encantadia',
            'first_time_login' => false,
            'last_login_at' => now(),
            'verification_code' => null,
            'verification_expires_at' => null,
            'last_otp_sent_at' => null,
            'roles' => 'user',
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
