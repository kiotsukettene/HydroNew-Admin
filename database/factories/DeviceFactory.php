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
            'device_name' => fake()->randomElement($deviceTypes) . ' ' . fake()->numberBetween(100, 999),
            'serial_number' => strtoupper(fake()->bothify('???-####-?????')),
            'model' => fake()->randomElement(['Model A', 'Model B', 'Model C', 'Model X']),
            'firmware_version' => fake()->randomElement(['1.0.0', '1.1.0', '2.0.0', '2.1.5']),
            'status' => fake()->randomElement(['online', 'offline']),
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
     * Indicate that the device is online.
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'online',
        ]);
    }

    /**
     * Indicate that the device is offline.
     */
    public function offline(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'offline',
        ]);
    }
}
