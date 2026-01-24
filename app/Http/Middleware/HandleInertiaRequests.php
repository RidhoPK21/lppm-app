<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $roles = [];

        // --- LOGIKA EKSTRAKSI ROLE ---
        if ($user) {
            // 1. Cek Relasi 'hakAkses' (Sesuai User Model Anda)
            if (method_exists($user, 'hakAkses')) {
                // Ambil kolom 'akses' yang mungkin berisi "Admin,Lppm Ketua"
                $rawAkses = $user->hakAkses->pluck('akses')->filter();

                // Loop dan pecah string berdasarkan koma
                foreach ($rawAkses as $aksesString) {
                    // Explode: "Admin, Lppm Ketua" -> ["Admin", "Lppm Ketua"]
                    $splitRoles = explode(',', $aksesString);
                    
                    // Bersihkan spasi (trim) dan gabungkan ke array utama
                    foreach ($splitRoles as $role) {
                        $roles[] = trim($role);
                    }
                }
            }

            // 2. Fallback: Cek kolom 'role' di tabel user (jika ada)
            if (empty($roles) && !empty($user->role)) {
                $roles = [$user->role];
            }
            
            // 3. Pastikan tidak ada duplikat dan index array rapi
            $roles = array_values(array_unique($roles));
        }

        return [
            ...parent::share($request),
            
            // Kirim ke Frontend (Inertia)
            'auth' => [
                'user' => $user,
                'roles' => $roles, // Array: ["Admin", "Lppm Ketua", "Dosen"]
                'akses' => $roles, 
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