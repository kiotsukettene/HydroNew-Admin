<?php

namespace Database\Factories;

use App\Models\HydroponicSetup;
use App\Models\HydroponicYield;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HydroponicYield>
 */
class HydroponicYieldFactory extends Factory
{
    protected $model = HydroponicYield::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hydroponic_setup_id' => HydroponicSetup::factory(),
            'total_weight' => fake()->randomFloat(2, 1, 50),
            'total_count' => fake()->numberBetween(10, 500),
            'notes' => fake()->optional()->sentence(),
            'is_archived' => false,
        ];
    }

    /**
     * Indicate that the yield is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
        ]);
    }
}
