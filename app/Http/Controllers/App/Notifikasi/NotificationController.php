<?php

namespace App\Http\Controllers\App\Notifikasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $authUser = $request->attributes->get('auth');
        
        // Ambil user Laravel berdasarkan email untuk mendapatkan ID lokal
        $laravelUser = \App\Models\User::where('email', $authUser->email)->first();
        
        if ($laravelUser) {
            // Cek dan buat notifikasi selamat datang jika belum ada
            $this->createWelcomeNotification($authUser->id, $laravelUser->id);
        }
        
        // Get filter & search params
        $search = $request->input('search', '');
        $filter = $request->input('filter', 'semua');
        $sort = $request->input('sort', 'terbaru');

        // Query notifications dari database
        $query = DB::table('notifications')
            ->where('user_id', $authUser->id);

        // Apply search
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Apply filter
        if ($filter === 'belum_dibaca') {
            $query->where('is_read', false);
        } elseif ($filter !== 'semua') {
            $query->where('type', $filter);
        }

        // Apply sort
        if ($sort === 'terbaru') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('created_at', 'asc');
        }

        // Get notifications dan convert to array
        $notifications = $query->get()->map(function($notif) {
            return [
                'id' => (int) $notif->id,
                'user_id' => $notif->user_id,
                'title' => $notif->title,
                'message' => $notif->message,
                'type' => $notif->type,
                'is_read' => (bool) $notif->is_read,
                'created_at' => $notif->created_at,
                'updated_at' => $notif->updated_at
            ];
        })->toArray();

        return Inertia::render('app/notifikasi/page', [
            'notifications' => $notifications,
            'filters' => [
                'search' => $search,
                'filter' => $filter,
                'sort' => $sort
            ]
        ]);
    }

    private function createWelcomeNotification($apiUserId, $laravelUserId)
    {
        // Hapus semua notifikasi welcome lama untuk user ini (baik System maupun Info)
        DB::table('notifications')
            ->where('user_id', $apiUserId)
            ->where(function($query) {
                $query->where('title', 'like', '%Selamat Datang%')
                      ->orWhere('title', 'like', '%Welcome%')
                      ->orWhere('message', 'like', '%lengkapi profil%');
            })
            ->delete();

        // Ambil data profile user berdasarkan Laravel user_id
        $profile = DB::table('profiles')
            ->where('user_id', $laravelUserId)
            ->first();

        if (!$profile) {
            return; // Profile tidak ditemukan
        }

        // Cek kelengkapan profil - semua field harus terisi dan tidak null
        $isProfileComplete = !empty($profile->nidn) 
            && !empty($profile->prodi) 
            && !empty($profile->sinta_id) 
            && !empty($profile->scopus_id);

        $title = 'Selamat Datang di Website LPPM';
        $message = '';

        if ($isProfileComplete) {
            $message = 'Selamat datang di Website LPPM.';
        } else {
            $message = 'Selamat datang, Silahkan Melengkapi Profilmu untuk pengalaman yang lebih baik.';
        }

        // Buat notifikasi baru (hanya 1)
        DB::table('notifications')->insert([
            'user_id' => $apiUserId,
            'title' => $title,
            'message' => $message,
            'type' => 'System',
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $authUser = $request->attributes->get('auth');
        
        $updated = DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $authUser->id)
            ->update([
                'is_read' => true, 
                'updated_at' => now()
            ]);

        if ($updated) {
            return redirect()->back()->with('success', 'Notifikasi ditandai sudah dibaca');
        }

        return redirect()->back()->with('error', 'Gagal menandai notifikasi');
    }

    public function markAllAsRead(Request $request)
    {
        $authUser = $request->attributes->get('auth');
        
        DB::table('notifications')
            ->where('user_id', $authUser->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true, 
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 'Semua notifikasi ditandai sudah dibaca');
    }
}