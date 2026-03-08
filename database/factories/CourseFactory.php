<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->jobTitle();
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraphs(3, true),
            'thumbnail' => 'https://placehold.co/56x56',
            'price' => fake()->randomFloat(2, 10, 100),
            'discount_code' => fake()->optional()->bothify('DISCOUNT-####'),
            'instructor_id' => User::factory()->instructor(),
            'course_code' => fake()->unique()->bothify('XLAE-####'),
            'max_students' => fake()->numberBetween(10, 200),
            'status' => 'published',
            'tags' => implode(',', fake()->words(3)),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}
