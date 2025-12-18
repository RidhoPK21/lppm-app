<?php

namespace Database\Factories;

use App\Models\BookAuthor;
use App\Models\BookSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookAuthor>
 */
class BookAuthorFactory extends Factory
{
    protected $model = BookAuthor::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'book_submission_id' => BookSubmission::factory(),
            'user_id' => $this->faker->optional(0.5)->uuid(),
            'name' => $this->faker->name(),
            'role' => $this->faker->randomElement(['FIRST', 'MEMBER', 'CORRESPONDING']),
            'affiliation' => $this->faker->randomElement(['Institut Teknologi Del', $this->faker->company()]),
        ];
    }
}