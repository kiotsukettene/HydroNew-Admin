<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    protected $model = Device::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $deviceTypes = [
            'pH Sensor',
            'TDS Meter',
            'Water Pump',
            'Temperature Sensor',
            'Humidity Sensor',
            'Light Sensor',
            'EC Meter',
            'DO Meter',
            'Flow Meter',
            'Control Unit'
        ];

        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement($deviceTypes) . ' ' . fake()->numberBetween(100, 999),
            'serial_number' => strtoupper(fake()->bothify('???-####-?????')),
            'status' => fake()->randomElement(['connected', 'not connected']),
            'is_archived' => false,
        ];
    }

    /**
     * Indicate that the device is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
        ]);
    }

    /**
     * Indicate that the device is connected.
     */
    public function connected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'connected',
        ]);
    }

    /**
     * Indicate that the device is not connected.
     */
    public function disconnected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'not connected',
        ]);
    }
}
