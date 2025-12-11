<?php

namespace App\Http\Controllers\App\Profile;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Menampilkan data profil user (gabungan Auth + DB)
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userId = $user->id;

        // Ambil data dari database (profile).
        // Jika profile BELUM ADA, buat entry baru.
        /** @var Profile $profile */
        $profile = Profile::firstOrCreate(
            ['user_id' => $userId],
            [
                // Gunakan name dari Auth user saat pertama kali dibuat
                'name' => $user->name ?? 'Nama Tidak Ditemukan',
            ]
        );

        // Merge data untuk frontend
        $merged = [
            // Data dari Auth User (Sumber utama, tidak bisa diedit)
            'name' => $user->name ?? $profile->name ?? 'User Default',
            'email' => $user->email ?? 'email@kosong.com',
            'photo' => $user->photo ?? '/images/default-avatar.png',

            // Data yang bisa diedit (dari DB Profile).
            // Pastikan Model Profile memiliki property ini (nidn, prodi, dll)
            'NIDN' => $profile->nidn ?? '',
            'ProgramStudi' => $profile->prodi ?? '',
            'SintaID' => $profile->sinta_id ?? '',
            'ScopusID' => $profile->scopus_id ?? '',
        ];

        Log::info('Profile Data Merged:', $merged);

        return Inertia::render('Profile/Index', [
            'user' => $merged,
        ]);
    }

    /**
     * Update data profil user (hanya field editable)
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'NIDN' => 'nullable|string|max:255',
            'Prodi' => 'nullable|string|max:255',
            'SintaID' => 'nullable|string|max:255',
            'ScopusID' => 'nullable|string|max:255',
        ]);

        $profile = Profile::firstOrNew(['user_id' => $user->id]);

        $profile->nidn = $validated['NIDN'] ?? null;
        $profile->prodi = $validated['Prodi'] ?? null;
        $profile->sinta_id = $validated['SintaID'] ?? null;
        $profile->scopus_id = $validated['ScopusID'] ?? null;

        $profile->save();

        return back()->with('success', 'Profil akademik berhasil diperbarui!');
    }
}