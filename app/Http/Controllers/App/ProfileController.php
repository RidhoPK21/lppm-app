<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\ProfilDosenModel;
use App\Models\User;
use App\Models\HakAksesModel;
use App\Helper\ToolsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    // ====================================================
    // 1. TAMPILKAN HALAMAN PROFIL
    // ====================================================
        public function index(Request $request) // Inject Request here
        {
            // FIX: Ambil user dari Request attributes yang diset oleh Middleware
            // Ini lebih aman daripada Auth::user() jika session belum persist sempurna
            $user = $request->attributes->get('auth') ?? Auth::user();

            // Safety check final
            if (!$user) {
                return redirect()->route('auth.login');
            }
            
            // Pastikan Profil Dosen ada (Create if not exists)
            $profil = ProfilDosenModel::firstOrCreate(
                ['user_id' => $user->id],
                ['id' => ToolsHelper::generateId()]
            );

            return Inertia::render('app/profile/profile-page', [
                'profil' => $profil, 
                'user' => $user,     
                'pageName' => 'Pengaturan Akun'
            ]);
        }

        // ====================================================
        // 2. SIMPAN PERUBAHAN (UPDATE)
        // ====================================================
        public function update(Request $request)
    {
        // Validasi input
        $request->validate([
            'nidn' => 'nullable|string|max:50',
            'prodi' => 'nullable|string|max:100',
            'sinta_id' => 'nullable|string|max:100',
            'scopus_id' => 'nullable|string|max:100',
            'photo' => 'nullable|image|max:2048', 
        ]);

        DB::beginTransaction();
        try {
            // [FIX UTAMA] Gunakan logika yang SAMA dengan function index()
            // Jangan pakai Auth::id() jika di index pakai request attributes
            $user = $request->attributes->get('auth') ?? Auth::user();

            // Safety check jika user tidak ditemukan (session habis/masalah middleware)
            if (!$user) {
                return redirect()->route('auth.login')->with('error', 'Sesi tidak valid.');
            }

            // Ambil profil berdasarkan ID user yang konsisten tadi
            // Gunakan firstOrCreate juga disini untuk jaga-jaga jika record belum ada
            $profil = ProfilDosenModel::firstOrCreate(
                ['user_id' => $user->id],
                ['id' => ToolsHelper::generateId()]
            );

            // A. LOGIKA UPLOAD FOTO
            if ($request->hasFile('photo')) {
                // Cek foto lama dari objek $user yang konsisten
                if ($user->photo && !str_contains($user->photo, 'ui-avatars.com')) {
                    $oldPath = str_replace('/storage/', '', $user->photo);
                    Storage::disk('public')->delete($oldPath);
                }

                $path = $request->file('photo')->store('photos', 'public');
                
                // Update tabel user (Pastikan update record yang benar)
                User::where('id', $user->id)->update(['photo' => '/storage/' . $path]);
            }

            // B. LOGIKA UPDATE DATA AKADEMIK
            $profil->update([
                'nidn' => $request->nidn,
                'prodi' => $request->prodi,
                'sinta_id' => $request->sinta_id,
                'scopus_id' => $request->scopus_id,
            ]);

            DB::commit();
            return back()->with('success', 'Profil berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan profil: ' . $e->getMessage());
        }
    }
}