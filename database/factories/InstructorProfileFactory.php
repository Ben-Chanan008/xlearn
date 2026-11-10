<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InstructorProfile>
 */
class InstructorProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->instructor(),
            'headline' => fake()->randomElement([
                'Full stack developer and patient teacher',
                'Data science mentor',
                'Product designer turned educator',
                'Cloud engineer who loves the basics',
            ]),
            'bio' => fake()->paragraphs(2, true),
            'expertise' => fake()->randomElements(['Laravel', 'PHP', 'JavaScript', 'Vue', 'SQL', 'Python', 'UI design', 'DevOps'], 3),
            'years_experience' => fake()->numberBetween(1, 20),
            'website' => fake()->url(),
            'linkedin' => 'https://www.linkedin.com/in/' . fake()->userName(),
        ];
    }

    /**
     * Just signed up: nothing filled in yet
     */
    public function blank(): static
    {
        return $this->state(fn () => [
            'headline' => null,
            'bio' => null,
            'expertise' => null,
            'years_experience' => null,
            'website' => null,
            'linkedin' => null,
        ]);
    }
}
