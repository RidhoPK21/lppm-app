<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
     
        
        // Membersihkan tabel users terakhir
        
        
        // --- LOGIKA PENYEMAAN OTENTIKASI HANYA BERDASARKAN ID UTAMA ---
        
        // 1. Ambil ID User Utama dari .env (ID Anda)
        $mainUserId = env('DEV_DEFAULT_USER_ID', '12e091b8-f227-4a58-8061-dc4a100c60f1');

        // 2. Buat User Utama (HANYA jika belum ada)
        // Kita tidak mengisi nama/email DUMMY di sini. 
        // Nama asli akan diisi saat Provisioning JIT (Anda login).
        User::firstOrCreate(
            ['id' => $mainUserId], // Kriteria Pencarian
            [ // Data minimal yang akan diisi jika tidak ditemukan
                'name' => 'Placeholder User', // Nama sementara yang akan ditimpa SSO
                'email' => 'placeholder@example.com',
                'password' => Hash::make('password'),
            ]
        );

        // --- HAPUS SEMUA PEMBUATAN USER DUMMY (Budi, Siti, Rahmat) ---
        // Anda tidak perlu lagi User::create untuk ID 22e..., 33e..., 44e...

        // 3. SEEDING HAK AKSES (Penting!)
        // Lanjutkan dengan seeding Hak Akses (yang seharusnya diurus oleh scripts/seed.ts)
        // Jika Anda memanggil scripts/seed.ts dari sini:
        // $this->call(HakAksesSeeder::class); 

        // Karena Anda memiliki Hak Akses di Sequelize, Anda harus memastikan 
        // skrip Sequelize Anda berjalan secara terpisah untuk mengisi m_hak_akses.
    }
}