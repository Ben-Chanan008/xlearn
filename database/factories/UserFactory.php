<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static string $password = 'Password12@';

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = ['M', 'F'];

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => Arr::random($gender),
            'email' => fake()->unique()->safeEmail(),
            'province' => 'SK',
            'password' => static::$password,
            'date_of_birth' => fake()->date(),
            'email_verified_at' => now(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'role' => 'student'
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'student',
        ]);
    }

    public function instructor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'instructor',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
}
