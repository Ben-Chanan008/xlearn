<?php

namespace Database\Factories;

use App\Models\CourseSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseSectionsContent>
 */
class CourseSectionsContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_section_id' => CourseSection::factory(),
            'content_name' => fake()->sentence(4),
            'content_information' => fake()->paragraphs(2, true),
            'files' => fake()->optional()->filePath(),
            'content_type' => fake()->randomElement(['assignment', 'information']),
        ];
    }

    public function assignment(): static
    {
        return $this->state(fn(array $attributes) => [
            'content_type' => 'assignment',
        ]);
    }

    public function information(): static
    {
        return $this->state(fn(array $attributes) => [
            'content_type' => 'information',
        ]);
    }
}