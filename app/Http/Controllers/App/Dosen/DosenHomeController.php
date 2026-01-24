<?php

namespace App\Http\Controllers\App\Dosen;

use App\Http\Controllers\Controller;
use App\Models\SeminarModel;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DosenHomeController extends Controller
{
    public function index()
    {
        // Mengambil 5 seminar terakhir milik dosen yang sedang login
        $recentSeminars = SeminarModel::where('dosen_profil_id', Auth::user()->profil->id ?? '') // Menggunakan relasi profil jika ada, atau fallback
                        ->orWhere('dosen_id', Auth::id()) // Fallback ke user_id jika kolom dosen_profil_id belum ready
                        ->orderBy('updated_at', 'desc')
                        ->limit(5)
                        ->get();

        // *Catatan: Pastikan SeminarModel Anda menggunakan kolom yang benar (dosen_profil_id atau user_id)*
        // Untuk amannya, sesuaikan query di atas dengan kolom di tabel t_seminar Anda.
        
        return Inertia::render('app/dosen/dosen-home-page', [
            'pageName' => 'Dashboard Dosen',
            'recentSeminars' => $recentSeminars 
        ]);
    }
}