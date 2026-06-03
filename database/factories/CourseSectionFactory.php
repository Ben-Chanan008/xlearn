<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseSectionsContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseSection>
 */
class CourseSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'section_name' => fake()->sentence(3),
        ];
    }

    public function withContents(int $count = 3): static
    {
        return $this->has(CourseSectionsContent::factory()->count($count), 'sectionContents');
    }
}