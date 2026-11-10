<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InstructorReview>
 */
class InstructorReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'student_id' => User::factory(),
            // The review is always for the course's own instructor
            'instructor_id' => fn (array $attributes) => Course::find($attributes['course_id'])->instructor_id,
            'stars' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional()->sentence(12),
        ];
    }
}
