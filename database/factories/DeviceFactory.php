<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => $this->faker->word() . ' Device',
            'serial_number' => 'SN-' . strtoupper($this->faker->unique()->bothify('??###???')),
            'status' => $this->faker->randomElement(['connected', 'not connected']),
        ];
    }
}
