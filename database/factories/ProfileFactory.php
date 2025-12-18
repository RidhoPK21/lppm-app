<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Mendapatkan user_id dari User Model yang ada atau membuat baru
        $userId = $this->faker->optional()->passthrough(
            User::factory()->create()->id
        );

        return [
            'id' => (string) Str::uuid(), // Harus UUID
            'user_id' => $userId, 
            'name' => $this->faker->name(),
            
            // Kolom-kolom akademik
            'nidn' => $this->faker->numerify('##########'), // 10 digit NIDN
            'prodi' => $this->faker->randomElement([
                'Teknik Informatika',
                'Sistem Informasi',
                'Teknik Elektro',
                'Manajemen Rekayasa',
                'Teknik Komputer',
            ]),
            'sinta_id' => $this->faker->numerify('#####'),
            'scopus_id' => $this->faker->numerify('###'),
        ];
    }

    /**
     * State untuk memastikan nama adalah nama user yang sudah ada
     */
    public function withUser(User $user): Factory
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'name' => $user->name,
        ]);
    }

    /**
     * State untuk membuat profile kosong (digunakan di ProfileControllerTest)
     */
    public function emptyProfile(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'name' => null,
            'nidn' => null,
            'prodi' => null,
            'sinta_id' => null,
            'scopus_id' => null,
        ]);
    }
}