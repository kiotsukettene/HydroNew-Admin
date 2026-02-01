<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'Momo',
                'last_name' => 'Revillame',
                'email' => 'kiotsuketteneloreto@gmail.com',
                'password' => bcrypt('Momorevillame@24'),
                'address' => '123 Quezon Avenue, Quezon City, Metro Manila',
                'status' => 'active',
                'is_archived' => false,
                'email_verified_at' => now()->subDays(120),
                'created_at' => now()->subDays(120),
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Dela Cruz',
                'email' => 'john.delacruz@example.com',
                'password' => bcrypt('password123'),
                'address' => '456 Rizal Street, Makati City, Metro Manila',
                'status' => 'active',
                'is_archived' => false,
                'email_verified_at' => now()->subDays(90),
                'created_at' => now()->subDays(90),
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'maria.santos@example.com',
                'password' => bcrypt('password123'),
                'address' => '789 Bonifacio Drive, Taguig City, Metro Manila',
                'status' => 'inactive',
                'is_archived' => false,
                'email_verified_at' => now()->subDays(60),
                'created_at' => now()->subDays(60),
            ],
            [
                'first_name' => 'Pedro',
                'last_name' => 'Garcia',
                'email' => 'pedro.garcia@example.com',
                'password' => bcrypt('password123'),
                'address' => '321 Luna Street, Manila City, Metro Manila',
                'status' => 'active',
                'is_archived' => false,
                'email_verified_at' => now()->subDays(45),
                'created_at' => now()->subDays(45),
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Reyes',
                'email' => 'ana.reyes@example.com',
                'password' => bcrypt('password123'),
                'address' => '654 Mabini Avenue, Pasig City, Metro Manila',
                'status' => 'active',
                'is_archived' => false,
                'email_verified_at' => null,
                'created_at' => now()->subDays(30),
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Mendoza',
                'email' => 'carlos.mendoza@example.com',
                'password' => bcrypt('password123'),
                'address' => '987 Aguinaldo Highway, Cavite City, Cavite',
                'status' => 'active',
                'is_archived' => true,
                'email_verified_at' => now()->subDays(180),
                'created_at' => now()->subDays(180),
            ],
            [
                'first_name' => 'Sofia',
                'last_name' => 'Torres',
                'email' => 'sofia.torres@example.com',
                'password' => bcrypt('password123'),
                'address' => '147 Del Pilar Street, San Juan City, Metro Manila',
                'status' => 'active',
                'is_archived' => false,
                'email_verified_at' => now()->subDays(15),
                'created_at' => now()->subDays(15),
            ],
            [
                'first_name' => 'Miguel',
                'last_name' => 'Aquino',
                'email' => 'miguel.aquino@example.com',
                'password' => bcrypt('password123'),
                'address' => '258 Roxas Boulevard, Pasay City, Metro Manila',
                'status' => 'inactive',
                'is_archived' => false,
                'email_verified_at' => now()->subDays(75),
                'created_at' => now()->subDays(75),
            ],
            [
                'first_name' => 'Isabel',
                'last_name' => 'Ramos',
                'email' => 'isabel.ramos@example.com',
                'password' => bcrypt('password123'),
                'address' => '369 Lacson Street, Bacolod City, Negros Occidental',
                'status' => 'active',
                'is_archived' => false,
                'email_verified_at' => null,
                'created_at' => now()->subDays(7),
            ],
            [
                'first_name' => 'Roberto',
                'last_name' => 'Cruz',
                'email' => 'roberto.cruz@example.com',
                'password' => bcrypt('password123'),
                'address' => '741 Magsaysay Avenue, Cebu City, Cebu',
                'status' => 'active',
                'is_archived' => false,
                'email_verified_at' => now()->subDays(3),
                'created_at' => now()->subDays(3),
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'email_verified_at' => $userData['email_verified_at'],
                    'password' => $userData['password'],
                    'address' => $userData['address'],
                    'role' => 'user',
                    'status' => $userData['status'],
                    'is_archived' => $userData['is_archived'],
                    'created_at' => $userData['created_at'],
                    'updated_at' => $userData['created_at'],
                ]
            );
        }
    }
}
