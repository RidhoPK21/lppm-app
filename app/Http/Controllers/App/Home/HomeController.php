<?php

namespace App\Http\Controllers\App\Home;

use App\Helper\ToolsHelper;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth; // 🔥 Wajib Import
use Illuminate\Support\Facades\DB;   // 🔥 Wajib Import

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil User yang sedang login
        $user = Auth::user();
        $roles = [];

        // 2. LOGIKA "BYPASS" (Ambil Role Langsung dari Database)
        // Kita tidak mengandalkan Middleware yang macet, kita ambil paksa disini.
        if ($user) {
            // Query ke tabel m_hak_akses menggunakan ID User (UUID)
            $rawDbData = DB::table('m_hak_akses')
                ->where('user_id', (string)$user->id)
                ->pluck('akses');

            // Pecah string role (contoh: "Admin,Lppm Ketua" -> ["Admin", "Lppm Ketua"])
            foreach ($rawDbData as $aksesString) {
                if (!empty($aksesString)) {
                    $parts = explode(',', $aksesString);
                    foreach ($parts as $p) {
                        $roles[] = trim($p);
                    }
                }
            }
            
            // Cek juga kolom 'role' bawaan tabel users (jaga-jaga)
            if (!empty($user->role)) {
                $roles[] = $user->role;
            }

            // Hapus duplikat
            $roles = array_values(array_unique($roles));
        }

        // 3. Susun Data Auth Manual
        $authData = [
            'user' => $user,
            'roles' => $roles, // <--- Ini yang ditunggu oleh Sidebar React
        ];

        // 4. Ambil Token (Kode Asli Anda)
        $authToken = ToolsHelper::getAuthToken();

        // 5. Render Halaman
        // Menggunakan path 'app/home/home-page' sesuai kode asli Anda
        return Inertia::render('app/home/home-page', [
            'auth' => $authData, // Kita kirim data yang sudah lengkap isinya
            'pageName' => 'Beranda',
            'authToken' => $authToken,
        ]);
    }
}