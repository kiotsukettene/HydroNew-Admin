<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\User;
use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feedback>
 */
class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Bug Report',
            'Feature Request',
            'General Inquiry',
            'Technical Support',
            'Complaint',
        ];

        return [
            'user_id' => User::factory(),
            'device_id' => Device::factory(),
            'category' => fake()->randomElement($categories),
            'subject' => fake()->sentence(),
            'message' => fake()->paragraph(3),
            'replied' => false,
        ];
    }

    /**
     * Indicate that the feedback has been replied to.
     */
    public function replied(): static
    {
        return $this->state(fn (array $attributes) => [
            'replied' => true,
        ]);
    }
}
