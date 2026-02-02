<?php

namespace Database\Factories;

use App\Models\HydroponicSetup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HydroponicSetup>
 */
class HydroponicSetupFactory extends Factory
{
    protected $model = HydroponicSetup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cropTypes = [
            'Lettuce',
            'Spinach',
            'Kale',
            'Basil',
            'Mint',
            'Tomato',
            'Cucumber',
            'Strawberry',
            'Pepper',
            'Arugula'
        ];

        $setupDate = fake()->dateTimeBetween('-60 days', 'now');

        return [
            'user_id' => User::factory(),
            'crop_name' => fake()->randomElement($cropTypes),
            'number_of_crops' => fake()->numberBetween(10, 100),
            'bed_size' => fake()->randomElement(['small', 'medium', 'large']),
            'pump_config' => fake()->optional()->words(3, true),
            'nutrient_solution' => fake()->optional()->words(3, true),
            'target_ph_min' => fake()->randomFloat(1, 5.5, 6.0),
            'target_ph_max' => fake()->randomFloat(1, 6.5, 7.0),
            'target_tds_min' => fake()->numberBetween(500, 800),
            'target_tds_max' => fake()->numberBetween(900, 1500),
            'harvest_status' => fake()->randomElement(['not_harvested', 'harvested', 'partial']),
            'harvest_date' => fake()->optional()->dateTimeBetween('now', '+60 days'),
            'water_amount' => fake()->optional()->randomElement(['low', 'medium', 'high']),
            'setup_date' => $setupDate,
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance']),
            'is_archived' => false,
        ];
    }

    /**
     * Indicate that the setup is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the setup is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
        ]);
    }
}
