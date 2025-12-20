<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NotificationFactory extends Factory
{
    /**
     * Nama model yang terkait dengan factory ini.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Definisikan state default untuk model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Menggunakan UUID jika tabel notifications Anda menggunakan UUID sebagai primary key
            'id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['System', 'Info', 'Peringatan', 'Sukses']),
            'is_read' => false,
            // Reference key bisa berupa null atau string random sesuai pattern di controller
            'reference_key' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * State untuk notifikasi yang sudah dibaca.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
        ]);
    }

    /**
     * State untuk notifikasi tipe sistem.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'System',
            'reference_key' => null,
        ]);
    }

    /**
     * State untuk notifikasi pengajuan buku (LPPM).
     */
    public function submission(int $bookId): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Pengajuan Buku Baru',
            'type' => 'Info',
            'reference_key' => 'SUBMISSION_'.$bookId,
        ]);
    }

    /**
     * State untuk undangan reviewer.
     */
    public function reviewerInvite(int $bookId, int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Undangan Review Buku',
            'type' => 'Info',
            'reference_key' => 'REVIEWER_INVITE_'.$bookId.'_'.$userId,
        ]);
    }
}
