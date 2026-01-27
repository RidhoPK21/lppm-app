<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\DB; // Wajib import ini

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $roles = [];

        if ($user) {
            // =========================================================
            // METODE BRUTE FORCE (LANGSUNG KE DATABASE)
            // Kita tidak pakai $user->hakAkses karena sering gagal load
            // =========================================================
            
            // 1. Ambil data mentah langsung dari tabel m_hak_akses
            // Pastikan nama tabel 'm_hak_akses' sesuai dengan database Anda
            $rawDbData = DB::table('m_hak_akses')
                        ->where('user_id', $user->id)
                        ->pluck('akses'); // Mengambil array string akses

            // 2. Loop dan Pecah String
            // Data dari DB: ["Admin,Lppm Ketua"]
            foreach ($rawDbData as $aksesString) {
                if (!empty($aksesString)) {
                    // Pecah jadi: ["Admin", "Lppm Ketua"]
                    $parts = explode(',', $aksesString);
                    foreach ($parts as $p) {
                        $roles[] = trim($p);
                    }
                }
            }

            // 3. Tambahkan fallback dari kolom 'role' di tabel users (jika ada)
            if (!empty($user->role)) {
                $roles[] = $user->role;
            }

            // 4. Pastikan Unik & Reset Index
            $roles = array_values(array_unique($roles));
        }

        return [
            ...parent::share($request),
            
            'auth' => [
                'user' => $user,
                // Kita kirim array roles yang sudah dipaksa isi
                'roles' => $roles, 
            ],
            
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'appName' => config('app.name'),
            'pageName' => $request->header('X-Page-Name'),
        ];
    }
}