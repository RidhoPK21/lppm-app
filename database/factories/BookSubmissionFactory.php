<?php

namespace Database\Factories;

use App\Models\BookSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookSubmission>
 */
class BookSubmissionFactory extends Factory
{
    protected $model = BookSubmission::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'user_id' => User::factory(), 
            'title' => $this->faker->sentence(4),
            'isbn' => $this->faker->isbn13(),
            'publication_year' => $this->faker->numberBetween(2000, date('Y')),
            'publisher' => $this->faker->company(),
            'publisher_level' => $this->faker->randomElement(['NATIONAL', 'INTERNATIONAL', 'NATIONAL_ACCREDITED']),
            'book_type' => $this->faker->randomElement(['TEACHING', 'REFERENCE', 'MONOGRAPH', 'CHAPTER']),
            'total_pages' => $this->faker->numberBetween(50, 500),
            'status' => $this->faker->randomElement(['DRAFT', 'SUBMITTED']),
        ];
    }
}