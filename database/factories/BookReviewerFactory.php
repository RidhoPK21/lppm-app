<?php

namespace Database\Factories;

use App\Models\BookReviewer;
use App\Models\BookSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookReviewerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BookReviewer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_submission_id' => BookSubmission::factory(),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['PENDING', 'ACCEPTED', 'REJECTED']),
            'note' => $this->faker->optional()->sentence,
            'invited_by' => User::factory(),
            'invited_at' => now(),
            'reviewed_at' => null,
            // 'is_paid' => false,
        ];
    }

    public function accepted()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ACCEPTED',
            'reviewed_at' => now(),
            'note' => $this->faker->paragraph,
        ]);
    }
}
