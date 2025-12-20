<?php

namespace Database\Factories;

use App\Models\BookSubmission;
use App\Models\SubmissionLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubmissionLog>
 */
class SubmissionLogFactory extends Factory
{
    protected $model = SubmissionLog::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'book_submission_id' => BookSubmission::factory(),
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement(['SUBMIT', 'CREATE_DRAFT', 'UPLOAD_DOCUMENTS']),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
