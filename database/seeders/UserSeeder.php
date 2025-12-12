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
        // Create or update the default user
        User::updateOrCreate(
            ['email' => 'admin@momo.com'],
            [
                'first_name' => 'Oreo',
                'last_name' => 'Cheesecake',
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
            ]
        );

        // Create 30 additional users
        $users = [
            ['first_name' => 'Maria', 'last_name' => 'Santos', 'address' => 'Manila, Philippines'],
            ['first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'address' => 'Quezon City, Philippines'],
            ['first_name' => 'Ana', 'last_name' => 'Garcia', 'address' => 'Makati City, Philippines'],
            ['first_name' => 'Pedro', 'last_name' => 'Reyes', 'address' => 'Cebu City, Philippines'],
            ['first_name' => 'Rosa', 'last_name' => 'Mendoza', 'address' => 'Davao City, Philippines'],
            ['first_name' => 'Carlos', 'last_name' => 'Ramos', 'address' => 'Pasig City, Philippines'],
            ['first_name' => 'Elena', 'last_name' => 'Torres', 'address' => 'Taguig City, Philippines'],
            ['first_name' => 'Miguel', 'last_name' => 'Cruz', 'address' => 'Caloocan City, Philippines'],
            ['first_name' => 'Sofia', 'last_name' => 'Flores', 'address' => 'Las Piñas City, Philippines'],
            ['first_name' => 'Jose', 'last_name' => 'Gonzales', 'address' => 'Parañaque City, Philippines'],
            ['first_name' => 'Carmen', 'last_name' => 'Morales', 'address' => 'Valenzuela City, Philippines'],
            ['first_name' => 'Rafael', 'last_name' => 'Rivera', 'address' => 'Marikina City, Philippines'],
            ['first_name' => 'Isabel', 'last_name' => 'Castillo', 'address' => 'Muntinlupa City, Philippines'],
            ['first_name' => 'Luis', 'last_name' => 'Aquino', 'address' => 'Mandaluyong City, Philippines'],
            ['first_name' => 'Teresa', 'last_name' => 'Villanueva', 'address' => 'San Juan City, Philippines'],
            ['first_name' => 'Ricardo', 'last_name' => 'Hernandez', 'address' => 'Pasay City, Philippines'],
            ['first_name' => 'Patricia', 'last_name' => 'Bautista', 'address' => 'Navotas City, Philippines'],
            ['first_name' => 'Antonio', 'last_name' => 'Santiago', 'address' => 'Malabon City, Philippines'],
            ['first_name' => 'Cristina', 'last_name' => 'Lopez', 'address' => 'Bacoor, Cavite'],
            ['first_name' => 'Fernando', 'last_name' => 'Martinez', 'address' => 'Imus, Cavite'],
            ['first_name' => 'Lucia', 'last_name' => 'Perez', 'address' => 'Dasmariñas, Cavite'],
            ['first_name' => 'Roberto', 'last_name' => 'Diaz', 'address' => 'General Trias, Cavite'],
            ['first_name' => 'Angela', 'last_name' => 'Fernandez', 'address' => 'Calamba, Laguna'],
            ['first_name' => 'Eduardo', 'last_name' => 'Alvarez', 'address' => 'Santa Rosa, Laguna'],
            ['first_name' => 'Gloria', 'last_name' => 'Jimenez', 'address' => 'Biñan, Laguna'],
            ['first_name' => 'Francisco', 'last_name' => 'Romero', 'address' => 'San Pedro, Laguna'],
            ['first_name' => 'Victoria', 'last_name' => 'Navarro', 'address' => 'Cabuyao, Laguna'],
            ['first_name' => 'Manuel', 'last_name' => 'Ruiz', 'address' => 'Antipolo, Rizal'],
            ['first_name' => 'Beatriz', 'last_name' => 'Salazar', 'address' => 'Cainta, Rizal'],
            ['first_name' => 'Gabriel', 'last_name' => 'Medina', 'address' => 'Taytay, Rizal'],
        ];

        foreach ($users as $index => $userData) {
            User::create([
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'email' => strtolower($userData['first_name'] . '.' . $userData['last_name']) . '@example.com',
                'email_verified_at' => rand(0, 1) ? now() : null,
                'password' => Hash::make('password123'),
                'profile_picture' => null,
                'address' => $userData['address'],
                'first_time_login' => rand(0, 1) ? true : false,
                'last_login_at' => now()->subDays(rand(0, 30)),
                'verification_code' => null,
                'verification_expires_at' => null,
                'last_otp_sent_at' => null,
                'roles' => 'user',
                'remember_token' => Str::random(10),
                'created_at' => now()->subDays(rand(0, 60)),
                'updated_at' => now()->subDays(rand(0, 30)),
            ]);
        }
    }
}
