<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the migrations.
     */
    public function run(): void
    {
        // 1. Ambil ID User Utama dari .env (ID Admin/Anda)
        $mainUserId = env('DEV_DEFAULT_USER_ID', '12e091b8-f227-4a58-8061-dc4a100c60f1');

        // Buat User Utama (Admin)

        // 2. Definisi User Dosen (Harus sama ID-nya dengan yang ada di seed.ts)
        $dosenUsers = [
            [
                'id' => '22e091b8-f227-4a58-8061-dc4a100c60f2',
                'name' => 'Budi Dosen',
                'email' => 'budi@del.ac.id',
            ],
            [
                'id' => '33e091b8-f227-4a58-8061-dc4a100c60f3',
                'name' => 'Siti Dosen',
                'email' => 'siti@del.ac.id',
            ],
            [
                'id' => '44e091b8-f227-4a58-8061-dc4a100c60f4',
                'name' => 'Rahmat Dosen',
                'email' => 'rahmat@del.ac.id',
            ],
        ];

        // Loop untuk membuat user dosen jika belum ada
        foreach ($dosenUsers as $user) {
            User::firstOrCreate(
                ['id' => $user['id']], // Cari berdasarkan ID (Primary Key)
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}