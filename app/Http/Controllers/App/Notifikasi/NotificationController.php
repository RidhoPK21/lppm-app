<?php

namespace App\Http\Controllers\App\Notifikasi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Tampilkan halaman notifikasi dan buat notifikasi yang diperlukan.
     */
    public function index(Request $request)
    {
        $authUser = $request->attributes->get('auth');
        
        // Ambil user Laravel berdasarkan email/ID dari API Auth
        $laravelUser = \App\Models\User::where('email', $authUser->email ?? null)->first();
        // Fallback jika email tidak ada, coba cari berdasarkan ID
        if (!$laravelUser && isset($authUser->id)) {
            $laravelUser = \App\Models\User::find($authUser->id);
        }
        
        $notifications = [];
        $filters = [
            'search' => $request->input('search', ''),
            'filter' => $request->input('filter', 'semua'),
            'sort' => $request->input('sort', 'terbaru')
        ];

        if (!$laravelUser) {
            Log::error('Laravel User not found for notification page access', ['api_auth' => $authUser]);
            return Inertia::render('app/notifikasi/page', [
                'notifications' => [], 
                'filters' => $filters,
                'booksForReview' => []
            ]);
        }

        // Ambil hak akses user dari user_id API (API Auth)
        $hakAkses = DB::table('m_hak_akses')
            ->where('user_id', $authUser->id)
            ->first();

        $userAccess = [];
        if ($hakAkses) {
            $userAccess = array_map('trim', explode(',', $hakAkses->akses));
        }

        // Cek apakah user adalah LPPM Staff atau Ketua
        $isLPPM = !empty(array_intersect(['Lppm Staff', 'Lppm Ketua'], $userAccess));
        
        // Cek apakah user adalah DOSEN
        $isDosen = !empty(array_intersect(['Dosen'], $userAccess));
        
        // Cek apakah user adalah HRD (case insensitive)
        $userAccessLower = array_map('strtolower', $userAccess);
        $isHRD = in_array('hrd', $userAccessLower);

        Log::info('User access check', [
            'user_id' => $laravelUser->id,
            'akses' => $userAccess,
            'is_lppm' => $isLPPM,
            'is_dosen' => $isDosen,
            'is_hrd' => $isHRD
        ]);

        // --- Proses Pembuatan Notifikasi ---
        $this->createWelcomeNotification($laravelUser->id);

        // Notifikasi untuk LPPM
        if ($isLPPM) {
            $this->createBookSubmissionNotifications($laravelUser->id);
            $this->createBookRevisionNotifications($laravelUser->id);
        }
        
        // Notifikasi untuk Dosen
        if ($isDosen) {
            $this->createBookRejectionNotifications($laravelUser->id);
            $this->createPaymentSuccessNotifications($laravelUser->id);
        }
        
        // Notifikasi untuk HRD
        if ($isHRD) {
            $this->createBookPaymentNotifications($laravelUser->id);
        }
        
        // ------------------------------------
        
        // Query notifications dari database menggunakan Laravel user_id
        // ✅ PERBAIKAN: Tambahkan filtering berdasarkan ROLE
        $query = DB::table('notifications')
            ->where('user_id', $laravelUser->id)
            ->where(function($q) use ($isLPPM, $isDosen, $isHRD) {
                // Selalu tampilkan notifikasi sistem/umum (Welcome, dll)
                $q->whereNull('reference_key')
                  ->orWhere('type', 'System');

                // Jika Role Dosen Aktif: Tampilkan notifikasi reject, uang cair, dan undangan review
                if ($isDosen) {
                    $q->orWhere('reference_key', 'like', 'REJECT_%')           
                      ->orWhere('reference_key', 'like', 'PAYMENT_SUCCESS_%')  
                      ->orWhere('reference_key', 'like', 'REVIEWER_INVITE_%'); 
                }

                // Jika Role LPPM Aktif: Tampilkan notifikasi pengajuan baru, revisi, dan hasil review
                if ($isLPPM) {
                    $q->orWhere('reference_key', 'like', 'SUBMISSION_%')       
                      ->orWhere('reference_key', 'like', 'REVISION_%')         
                      ->orWhere('reference_key', 'like', 'REVIEW_COMPLETE_%'); 
                }

                // Jika Role HRD Aktif: Tampilkan notifikasi pembayaran
                if ($isHRD) {
                    $q->orWhere('reference_key', 'like', 'PAYMENT_CHIEF_%');   
                }
            });

        // Apply search
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('message', 'like', "%{$filters['search']}%");
            });
        }

        // Apply filter
        if ($filters['filter'] === 'belum_dibaca') {
            $query->where('is_read', false);
        } elseif ($filters['filter'] !== 'semua') {
            $query->where('type', $filters['filter']);
        }

        // Apply sort
        if ($filters['sort'] === 'terbaru') {
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
                'updated_at' => $notif->updated_at,
                'reference_key' => $notif->reference_key
            ];
        })->toArray();

        // === TAMBAHAN: Ambil detail buku untuk notifikasi reviewer ===
        $booksForReview = [];
        
        // Filter notifikasi yang berisi undangan review
        $reviewInviteNotifs = array_filter($notifications, function($notif) {
            return strpos($notif['reference_key'] ?? '', 'REVIEWER_INVITE_') === 0;
        });
        
        foreach ($reviewInviteNotifs as $notif) {
            // Parse reference_key: REVIEWER_INVITE_{book_id}_{reviewer_id}
            $parts = explode('_', $notif['reference_key']);
            if (count($parts) >= 3) {
                $bookId = $parts[2];
                
                // Ambil detail buku dari database
                $bookDetail = DB::table('book_submissions as bs')
                    ->leftJoin('users as u', 'bs.user_id', '=', 'u.id')
                    ->where('bs.id', $bookId)
                    ->select(
                        'bs.id',
                        'bs.title',
                        'bs.isbn',
                        'bs.publisher',
                        'bs.drive_link',
                        'bs.created_at',
                        'u.name as user_name'
                    )
                    ->first();
                
                if ($bookDetail) {
                    $booksForReview[$notif['id']] = [
                        'id' => (int) $bookDetail->id,
                        'title' => $bookDetail->title,
                        'isbn' => $bookDetail->isbn,
                        'publisher' => $bookDetail->publisher,
                        'drive_link' => $bookDetail->drive_link,
                        'user_name' => $bookDetail->user_name,
                        'created_at' => $bookDetail->created_at
                    ];
                }
            }
        }

        Log::info('Notifications loaded with book details', [
            'user_id' => $laravelUser->id,
            'count' => count($notifications),
            'books_for_review' => count($booksForReview),
            'is_lppm' => $isLPPM,
            'is_dosen' => $isDosen,
            'is_hrd' => $isHRD
        ]);

        return Inertia::render('app/notifikasi/page', [
            'notifications' => $notifications,
            'filters' => $filters,
            'booksForReview' => $booksForReview
        ]);
    }
    
    // ... Sisa method (resolveLaravelUserIdWithMultipleSources, createIdMapping, dll) biarkan tetap sama ...
    private static function resolveLaravelUserIdWithMultipleSources($apiUserId)
    {
        Log::info('[Resolve User ID] Starting for API user ID', ['api_user_id' => $apiUserId]);
        
        // 1. Cek di mapping table
        $mapping = DB::table('user_id_mappings')
            ->where('api_user_id', $apiUserId)
            ->first();
        
        if ($mapping) {
            Log::info('[Resolve User ID] Found in mapping table', [
                'api_user_id' => $apiUserId,
                'laravel_user_id' => $mapping->laravel_user_id
            ]);
            return $mapping->laravel_user_id;
        }
        
        // 2. Cek apakah apiUserId ada sebagai user_id di users table
        $user = \App\Models\User::where('id', $apiUserId)->first();
        if ($user) {
            Log::info('[Resolve User ID] Found in users table with same ID', [
                'api_user_id' => $apiUserId,
                'laravel_user_id' => $user->id
            ]);
            
            // Buat mapping untuk future
            self::createIdMapping($apiUserId, $user->id);
            return $user->id;
        }
        
        // 3. Cari berdasarkan email pattern dari m_hak_akses atau API
        // Cari di m_hak_akses untuk mendapatkan info user
        $hakAkses = DB::table('m_hak_akses')
            ->where('user_id', $apiUserId)
            ->first();
            
        if ($hakAkses) {
            // Cari user berdasarkan nama atau pola email
            $user = \App\Models\User::where('email', 'like', '%' . $apiUserId . '%')
                ->orWhere('name', 'like', '%' . substr($apiUserId, 0, 8) . '%')
                ->first();
                
            if ($user) {
                Log::info('[Resolve User ID] Found by email/name pattern', [
                    'api_user_id' => $apiUserId,
                    'laravel_user_id' => $user->id
                ]);
                
                self::createIdMapping($apiUserId, $user->id);
                return $user->id;
            }
        }
        
        // 4. Jika tidak ditemukan sama sekali, gunakan API user_id sebagai Laravel user_id
        Log::warning('[Resolve User ID] Not found in any source, using API user ID as Laravel user ID', [
            'api_user_id' => $apiUserId
        ]);
        
        // Buat mapping dengan diri sendiri
        self::createIdMapping($apiUserId, $apiUserId);
        return $apiUserId;
    }
    
    /**
     * Buat mapping antara API ID dan Laravel ID
     */
    private static function createIdMapping($apiUserId, $laravelUserId)
    {
        try {
            DB::table('user_id_mappings')->updateOrInsert(
                ['api_user_id' => $apiUserId],
                [
                    'laravel_user_id' => $laravelUserId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            
            Log::info('[ID Mapping] Created/Updated mapping', [
                'api_user_id' => $apiUserId,
                'laravel_user_id' => $laravelUserId
            ]);
            
        } catch (\Exception $e) {
            Log::error('[ID Mapping] Failed to create mapping', [
                'error' => $e->getMessage()
            ]);
        }
    }
 public static function sendReviewerInvitationNotification($bookId, $bookTitle, $reviewerUserId)
{
    try {
        Log::info('[Reviewer Invitation] START', [
            'book_id' => $bookId,
            'book_title' => $bookTitle,
            'reviewer_user_id' => $reviewerUserId,
            'timestamp' => now()->toDateTimeString()
        ]);
        
        // === PERBAIKAN: Asumsikan $reviewerUserId sudah Laravel user_id ===
        $laravelUserId = $reviewerUserId;
        
        // Pastikan user ada
        $user = \App\Models\User::find($laravelUserId);
        
        if (!$user) {
            Log::error('[Reviewer Invitation] User not found', [
                'user_id' => $laravelUserId
            ]);
            return false;
        }

        Log::info('[Reviewer Invitation] User found', [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]);

        // Buat notifikasi
        $message = "Tugas Baru: Anda diundang me-review buku '{$bookTitle}'.";
        $referenceKey = 'REVIEWER_INVITE_' . $bookId . '_' . $laravelUserId;

        Log::info('[Reviewer Invitation] Preparing notification', [
            'user_id' => $laravelUserId,
            'reference_key' => $referenceKey,
            'message' => $message
        ]);

        // === PERBAIKAN: Hapus pengecekan exists, langsung insert atau update ===
        $inserted = DB::table('notifications')->updateOrInsert(
            [
                'user_id' => $laravelUserId,
                'reference_key' => $referenceKey
            ],
            [
                'title' => 'Undangan Review Buku',
                'message' => $message,
                'type' => 'Info',
                'is_read' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );
        
        Log::info('[Reviewer Invitation] Notification CREATED/UPDATED', [
            'inserted' => $inserted,
            'user_id' => $laravelUserId,
            'book_id' => $bookId,
            'reference_key' => $referenceKey
        ]);
        
        // Verifikasi
        $check = DB::table('notifications')
            ->where('reference_key', $referenceKey)
            ->first();
            
        Log::info('[Reviewer Invitation] Final verification', [
            'notification_exists' => $check ? 'YES' : 'NO',
            'notification_id' => $check->id ?? null
        ]);
        
        return true;

    } catch (\Exception $e) {
        Log::error('[Reviewer Invitation] ERROR: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'book_id' => $bookId,
            'reviewer_user_id' => $reviewerUserId
        ]);
        return false;
    }
}

      private static function getUserNameFromMultipleSources($apiUserId, $laravelUserId)
    {
        // 1. Cek di m_hak_akses via join dengan users jika ada
        $userInfo = DB::table('m_hak_akses as ha')
            ->leftJoin('users as u', 'u.id', '=', 'ha.user_id')
            ->where('ha.user_id', $apiUserId)
            ->select('u.name')
            ->first();
            
        if ($userInfo && $userInfo->name) {
            return $userInfo->name;
        }
        
        // 2. Cek di users table dengan laravelUserId
        $user = \App\Models\User::find($laravelUserId);
        if ($user && $user->name) {
            return $user->name;
        }
        
        // 3. Default name
        return 'Reviewer ' . substr($apiUserId, 0, 8);
    }

/**
 * Helper untuk resolve Laravel user_id dari API user_id
 */
private static function resolveLaravelUserId($apiUserId)
{
    // 1. Cek di mapping table
    $mapping = DB::table('user_id_mappings')
        ->where('api_user_id', $apiUserId)
        ->first();
    
    if ($mapping) {
        return $mapping->laravel_user_id;
    }
    
    // 2. Cek apakah apiUserId ada sebagai user_id di users table
    $user = \App\Models\User::where('id', $apiUserId)->first();
    if ($user) {
        return $user->id;
    }
    
    // 3. Cari berdasarkan email pattern
    $user = \App\Models\User::where('email', 'like', '%' . $apiUserId . '%')->first();
    if ($user) {
        // Buat mapping untuk future
        DB::table('user_id_mappings')->insert([
            'api_user_id' => $apiUserId,
            'laravel_user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return $user->id;
    }
    
    // 4. Return apiUserId sebagai fallback
    return $apiUserId;
}
    private function createWelcomeNotification($laravelUserId)
    {
        // Cek apakah sudah ada notifikasi welcome
        $exists = DB::table('notifications')
            ->where('user_id', $laravelUserId)
            ->where('title', 'Selamat Datang di Website LPPM')
            ->exists();

        if ($exists) {
            return;
        }

        // Ambil data profile user (minimal untuk mengecek kelengkapan)
        $profile = DB::table('profiles')
            ->where('user_id', $laravelUserId)
            ->first();

        // Cek kelengkapan profil
        $isProfileComplete = $profile && !empty($profile->nidn) 
            && !empty($profile->prodi) 
            && !empty($profile->sinta_id) 
            && !empty($profile->scopus_id);

        $title = 'Selamat Datang di Website LPPM';
        $message = $isProfileComplete 
            ? 'Selamat datang di Website LPPM. Anda siap untuk mengajukan buku.' 
            : 'Selamat datang, Silahkan Melengkapi Profilmu untuk pengalaman yang lebih baik.';

        // Buat notifikasi baru
        DB::table('notifications')->insert([
            'user_id' => $laravelUserId,
            'title' => $title,
            'message' => $message,
            'type' => 'System',
            'is_read' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }

    /**
     * Membuat notifikasi untuk Staff/Ketua LPPM tentang pengajuan buku baru (status SUBMITTED yang belum pernah di-reject).
     * HANYA untuk user LPPM yang sedang login.
     */
    private function createBookSubmissionNotifications($laravelUserId)
    {
        try {
            // Ambil buku dengan status SUBMITTED yang TIDAK memiliki reject_note (belum pernah di-reject)
            $submittedBooks = DB::table('book_submissions')
                ->select('id', 'title', 'user_id') 
                ->where('status', 'SUBMITTED')
                ->whereNull('reject_note')
                ->get();

            if ($submittedBooks->isEmpty()) {
                return;
            }

            // Kumpulkan user_id pengaju untuk query batch nama dosen
            $submitterIds = $submittedBooks->pluck('user_id')->unique()->toArray();
            $dosenUsers = DB::table('users')
                ->whereIn('id', $submitterIds)
                ->pluck('name', 'id');

            foreach ($submittedBooks as $book) {
                $dosenName = $dosenUsers[$book->user_id] ?? 'Dosen';
                $messageFormat = "{$dosenName} mengirim buku '{$book->title}'. Segera verifikasi.";
                
                $referenceKey = 'SUBMISSION_' . $book->id;
                
                $notifExists = DB::table('notifications')
                    ->where('user_id', $laravelUserId)
                    ->where('reference_key', $referenceKey)
                    ->exists();

                if ($notifExists) {
                    continue;
                }

                DB::table('notifications')->insert([
                    'user_id' => $laravelUserId,
                    'title' => 'Pengajuan Buku Baru',
                    'message' => $messageFormat,
                    'type' => 'Info',
                    'is_read' => false,
                    'reference_key' => $referenceKey,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating book submission notifications', [
                'lppm_user_id' => $laravelUserId,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Membuat notifikasi untuk Staff/Ketua LPPM tentang revisi buku (status SUBMITTED yang pernah di-reject).
     * HANYA untuk user LPPM yang sedang login.
     */
    private function createBookRevisionNotifications($laravelUserId)
    {
        try {
            $revisionBooks = DB::table('book_submissions')
                ->select('id', 'title', 'user_id', 'reject_note') 
                ->where('status', 'SUBMITTED')
                ->whereNotNull('reject_note')
                ->get();

            if ($revisionBooks->isEmpty()) {
                return;
            }

            $submitterIds = $revisionBooks->pluck('user_id')->unique()->toArray();
            $dosenUsers = DB::table('users')
                ->whereIn('id', $submitterIds)
                ->pluck('name', 'id');

            foreach ($revisionBooks as $book) {
                $dosenName = $dosenUsers[$book->user_id] ?? 'Dosen';
                $messageFormat = "Revisi buku '{$book->title}' dari {$dosenName} perlu ditindaklanjuti.";
                
                $referenceKey = 'REVISION_' . $book->id;
                
                $notifExists = DB::table('notifications')
                    ->where('user_id', $laravelUserId)
                    ->where('reference_key', $referenceKey)
                    ->exists();

                if ($notifExists) {
                    continue;
                }

                DB::table('notifications')->insert([
                    'user_id' => $laravelUserId,
                    'title' => 'Revisi Pengajuan Buku',
                    'message' => $messageFormat,
                    'type' => 'Peringatan',
                    'is_read' => false,
                    'reference_key' => $referenceKey,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating book revision notifications', [
                'lppm_user_id' => $laravelUserId,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Membuat notifikasi untuk Dosen tentang buku yang ditolak (status REJECTED miliknya).
     * HANYA untuk user Dosen yang sedang login.
     */
    private function createBookRejectionNotifications($laravelUserId)
    {
        try {
            $rejectedBooks = DB::table('book_submissions')
                ->select('id', 'title', 'reject_note', 'rejected_by') 
                ->where('status', 'REJECTED')
                ->where('user_id', $laravelUserId)
                ->get();

            if ($rejectedBooks->isEmpty()) {
                return;
            }

            foreach ($rejectedBooks as $book) {
                $rejectNote = $book->reject_note ?? 'Tidak ada catatan';
                $rejectedBy = $book->rejected_by;
                
                $rejectorRole = null;
                if ($rejectedBy) {
                    $hakAkses = DB::table('m_hak_akses')
                        ->where('user_id', $rejectedBy)
                        ->first();
                    
                    if ($hakAkses) {
                        $aksesArray = array_map('trim', explode(',', $hakAkses->akses));
                        if (in_array('Lppm Ketua', $aksesArray)) {
                            $rejectorRole = 'Lppm Ketua';
                        } elseif (in_array('Lppm Staff', $aksesArray)) {
                            $rejectorRole = 'Lppm Staff';
                        }
                    }
                }
                
                if ($rejectorRole === 'Lppm Ketua') {
                    $title = 'Pengajuan Ditolak';
                    $messageFormat = "Ditolak: Maaf, pengajuan buku '{$book->title}' belum disetujui.";
                } else {
                    $title = 'Revisi Diperlukan';
                    $messageFormat = "Revisi: Dokumen buku '{$book->title}' perlu diperbaiki. Cek catatan: {$rejectNote}";
                }
                
                $referenceKey = 'REJECT_' . $book->id;
                
                $notifExists = DB::table('notifications')
                    ->where('user_id', $laravelUserId)
                    ->where('reference_key', $referenceKey)
                    ->exists();

                if ($notifExists) {
                    continue;
                }

                DB::table('notifications')->insert([
                    'user_id' => $laravelUserId,
                    'title' => $title,
                    'message' => $messageFormat,
                    'type' => 'Peringatan',
                    'is_read' => false,
                    'reference_key' => $referenceKey,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating book rejection notifications', [
                'user_id' => $laravelUserId,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Membuat notifikasi untuk HRD tentang buku yang statusnya APPROVED_CHIEF.
     * HANYA untuk user HRD yang sedang login.
     */
    private function createBookPaymentNotifications($laravelUserId)
    {
        try {
            Log::info('[HRD Notification] Starting to create payment notifications', [
                'user_id' => $laravelUserId
            ]);
            
            // Query buku dengan status APPROVED_CHIEF
            $approvedBooks = DB::table('book_submissions')
                ->select('id', 'title', 'approved_amount')
                ->where('status', 'APPROVED_CHIEF')
                ->whereNotNull('approved_amount')
                ->where('approved_amount', '>', 0)
                ->get();
            
            Log::info('[HRD Notification] Books query result', [
                'count' => $approvedBooks->count(),
                'books' => $approvedBooks->toArray()
            ]);
            
            if ($approvedBooks->isEmpty()) {
                Log::info('[HRD Notification] No APPROVED_CHIEF books found');
                return;
            }
            
            foreach ($approvedBooks as $book) {
                $nominal = 'Rp ' . number_format($book->approved_amount, 0, ',', '.');
                $message = "Bayar: Segera cairkan {$nominal} untuk buku '{$book->title}'.";
                $referenceKey = 'PAYMENT_CHIEF_' . $book->id;
                
                // Cek apakah notifikasi sudah ada
                $notifExists = DB::table('notifications')
                    ->where('user_id', $laravelUserId)
                    ->where('reference_key', $referenceKey)
                    ->exists();
                
                if (!$notifExists) {
                    DB::table('notifications')->insert([
                        'user_id' => $laravelUserId,
                        'title' => 'Pembayaran Penghargaan Buku',
                        'message' => $message,
                        'type' => 'Peringatan',
                        'is_read' => false,
                        'reference_key' => $referenceKey,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                    
                    Log::info('[HRD Notification] Created notification', [
                        'user_id' => $laravelUserId,
                        'book_id' => $book->id,
                        'book_title' => $book->title,
                        'amount' => $book->approved_amount,
                        'reference_key' => $referenceKey
                    ]);
                } else {
                    Log::info('[HRD Notification] Notification already exists', [
                        'user_id' => $laravelUserId,
                        'book_id' => $book->id
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('[HRD Notification] Error creating payment notifications', [
                'user_id' => $laravelUserId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Membuat notifikasi untuk Dosen tentang buku yang sudah dibayar (status PAID).
     * HANYA untuk user Dosen yang sedang login.
     * Notifikasi ini akan dicek setiap kali dosen masuk ke halaman notifikasi.
     */
    private function createPaymentSuccessNotifications($laravelUserId)
    {
        try {
            Log::info('[Dosen Payment Success] Starting to check payment success notifications', [
                'user_id' => $laravelUserId
            ]);
            
            // Query buku dengan status PAID yang dimiliki oleh dosen ini
            $paidBooks = DB::table('book_submissions')
                ->select('id', 'title', 'payment_date', 'approved_amount')
                ->where('status', 'PAID')
                ->where('user_id', $laravelUserId)
                ->whereNotNull('payment_date')
                ->get();
            
            Log::info('[Dosen Payment Success] Paid books query result', [
                'count' => $paidBooks->count(),
                'books' => $paidBooks->toArray()
            ]);
            
            if ($paidBooks->isEmpty()) {
                Log::info('[Dosen Payment Success] No PAID books found for this dosen');
                return;
            }
            
            foreach ($paidBooks as $book) {
                $message = "Selamat! Dana insentif buku '{$book->title}' sudah cair.";
                $referenceKey = 'PAYMENT_SUCCESS_' . $book->id;
                
                // Cek apakah notifikasi sudah ada
                $notifExists = DB::table('notifications')
                    ->where('user_id', $laravelUserId)
                    ->where('reference_key', $referenceKey)
                    ->exists();
                
                if (!$notifExists) {
                    DB::table('notifications')->insert([
                        'user_id' => $laravelUserId,
                        'title' => 'Dana Insentif Buku Telah Cair',
                        'message' => $message,
                        'type' => 'Sukses',
                        'is_read' => false,
                        'reference_key' => $referenceKey,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                    
                    Log::info('[Dosen Payment Success] Created notification', [
                        'user_id' => $laravelUserId,
                        'book_id' => $book->id,
                        'book_title' => $book->title,
                        'payment_date' => $book->payment_date,
                        'reference_key' => $referenceKey
                    ]);
                } else {
                    Log::info('[Dosen Payment Success] Notification already exists', [
                        'user_id' => $laravelUserId,
                        'book_id' => $book->id
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('[Dosen Payment Success] Error creating payment success notifications', [
                'user_id' => $laravelUserId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function markAsRead(Request $request, $id)
    {
        $authUser = $request->attributes->get('auth');
        $laravelUser = \App\Models\User::where('email', $authUser->email)->first();
        
        if (!$laravelUser) {
            return redirect()->back()->with('error', 'User tidak ditemukan');
        }

        $updated = DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $laravelUser->id)
            ->update([
                'is_read' => true, 
                'updated_at' => Carbon::now()
            ]);

        if ($updated) {
            return redirect()->back()->with('success', 'Notifikasi ditandai sudah dibaca');
        }

        return redirect()->back()->with('error', 'Gagal menandai notifikasi');
    }

    public function markAllAsRead(Request $request)
    {
        $authUser = $request->attributes->get('auth');
        $laravelUser = \App\Models\User::where('email', $authUser->email)->first();
        
        if (!$laravelUser) {
            return redirect()->back()->with('error', 'User tidak ditemukan');
        }

        DB::table('notifications')
            ->where('user_id', $laravelUser->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true, 
                'updated_at' => Carbon::now()
            ]);

        return redirect()->back()->with('success', 'Semua notifikasi ditandai sudah dibaca');
    }

    /**
     * Kirim notifikasi pengajuan buku baru ke SEMUA Staff & Ketua LPPM
     */
    public static function sendBookSubmissionNotification($bookId, $bookTitle, $submitterName)
    {
        try {
            $lppmUserIds = DB::table('m_hak_akses')
                ->where(function($query) {
                    $query->where('akses', 'like', '%Lppm Staff%')
                          ->orWhere('akses', 'like', '%Lppm Ketua%');
                })
                ->pluck('user_id')
                ->unique()
                ->toArray();
                
            $lppmUsers = \App\Models\User::whereIn('id', $lppmUserIds)->pluck('id'); 

            $messageFormat = "{$submitterName} mengirim buku '{$bookTitle}'. Segera verifikasi.";
            $notificationsToInsert = [];
            
            $referenceKey = 'SUBMISSION_' . $bookId;

            foreach ($lppmUsers as $laravelUserId) {
                $notifExists = DB::table('notifications')
                    ->where('user_id', $laravelUserId)
                    ->where('reference_key', $referenceKey)
                    ->exists();

                if (!$notifExists) {
                    $notificationsToInsert[] = [
                        'user_id' => $laravelUserId,
                        'title' => 'Pengajuan Buku Baru',
                        'message' => $messageFormat,
                        'type' => 'Info',
                        'is_read' => false,
                        'reference_key' => $referenceKey,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
            }
            
            if (!empty($notificationsToInsert)) {
                DB::table('notifications')->insert($notificationsToInsert);
                Log::info('Batch notifications sent to LPPM users', ['count' => count($notificationsToInsert)]);
            }

        } catch (\Exception $e) {
            Log::error('Error in sendBookSubmissionNotification', [
                'book_id' => $bookId,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Kirim notifikasi penolakan buku ke Dosen pengaju
     */
    public static function sendBookRejectionNotification($bookId, $bookTitle, $submitterId, $rejectorId, $rejectNote = null)
    {
        try {
            if (empty($rejectorId)) {
                Log::error('rejectorId is required but not provided', [
                    'book_id' => $bookId,
                    'submitter_id' => $submitterId
                ]);
                return;
            }

            DB::table('book_submissions')
                ->where('id', $bookId)
                ->update([
                    'rejected_by' => $rejectorId,
                    'updated_at' => Carbon::now()
                ]);

            $laravelUser = \App\Models\User::find($submitterId);
            
            if (!$laravelUser) {
                Log::warning('Submitter user not found', ['submitter_id' => $submitterId]);
                return;
            }
            
            $rejectorRole = null;
            $hakAkses = DB::table('m_hak_akses')
                ->where('user_id', $rejectorId)
                ->first();
            
            if ($hakAkses) {
                $aksesArray = array_map('trim', explode(',', $hakAkses->akses));
                if (in_array('Lppm Ketua', $aksesArray)) {
                    $rejectorRole = 'Lppm Ketua';
                } elseif (in_array('Lppm Staff', $aksesArray)) {
                    $rejectorRole = 'Lppm Staff';
                }
            }
            
            if ($rejectorRole === 'Lppm Ketua') {
                $title = 'Pengajuan Ditolak';
                $message = "Ditolak: Maaf, pengajuan buku '{$bookTitle}' belum disetujui.";
            } else {
                $title = 'Revisi Diperlukan';
                $rejectNoteText = $rejectNote ?? 'Tidak ada catatan';
                $message = "Revisi: Dokumen buku '{$bookTitle}' perlu diperbaiki. Cek catatan: {$rejectNoteText}";
            }
            
            $referenceKey = 'REJECT_' . $bookId;
            
            $notifExists = DB::table('notifications')
                ->where('user_id', $laravelUser->id)
                ->where('reference_key', $referenceKey)
                ->exists();

            if (!$notifExists) {
                DB::table('notifications')->insert([
                    'user_id' => $laravelUser->id,
                    'title' => $title,
                    'message' => $message,
                    'type' => 'Peringatan',
                    'is_read' => false,
                    'reference_key' => $referenceKey,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in sendBookRejectionNotification', [
                'book_id' => $bookId,
                'error' => $e->getMessage(),
            ]);
        }
    }


    /**
 * Submit review dari reviewer
 */
 public function submitReview(Request $request, $bookId)
    {
        $request->validate([
            'note' => 'required|string|max:2000',
            'notification_id' => 'required|integer'
        ]);

        try {
            $authUser = $request->attributes->get('auth');
            $laravelUser = \App\Models\User::where('email', $authUser->email)->first();
            
            if (!$laravelUser && isset($authUser->id)) {
                $laravelUser = \App\Models\User::find($authUser->id);
            }
            
            if (!$laravelUser) {
                return response()->json(['error' => 'User tidak ditemukan'], 404);
            }

            DB::beginTransaction();

            // 1. Update book_reviewers table
            $bookReviewer = \App\Models\BookReviewer::where('book_submission_id', $bookId)
                ->where('user_id', $laravelUser->id)
                ->first();

            if (!$bookReviewer) {
                DB::rollBack();
                Log::error('[Submit Review] BookReviewer not found', [
                    'book_id' => $bookId,
                    'user_id' => $laravelUser->id
                ]);
                return response()->json(['error' => 'Data reviewer tidak ditemukan'], 404);
            }

            $bookReviewer->update([
                'note' => $request->note,
                'status' => 'ACCEPTED',
                'reviewed_at' => now()
            ]);

            Log::info('[Submit Review] BookReviewer updated', [
                'book_reviewer_id' => $bookReviewer->id,
                'book_id' => $bookId,
                'status' => 'ACCEPTED'
            ]);

            // 2. Mark notification as read
            DB::table('notifications')
                ->where('id', $request->notification_id)
                ->update([
                    'is_read' => true,
                    'updated_at' => now()
                ]);

            // 3. Get book details
            $book = \App\Models\BookSubmission::find($bookId);
            
            if (!$book) {
                DB::rollBack();
                return response()->json(['error' => 'Buku tidak ditemukan'], 404);
            }

            // 4. Send notification to LPPM Ketua (yang mengundang)
            if ($bookReviewer->invited_by) {
                $inviter = \App\Models\User::find($bookReviewer->invited_by);
                
                if ($inviter) {
                    $message = "Review Selesai: Hasil penilaian buku '{$book->title}' telah tersedia.";
                    $referenceKey = 'REVIEW_COMPLETE_' . $bookId . '_' . $laravelUser->id;

                    // Cek apakah notifikasi sudah ada
                    $existingNotif = DB::table('notifications')
                        ->where('reference_key', $referenceKey)
                        ->exists();

                    if (!$existingNotif) {
                        DB::table('notifications')->insert([
                            'user_id' => $inviter->id,
                            'title' => 'Review Buku Selesai',
                            'message' => $message,
                            'type' => 'Sukses',
                            'is_read' => false,
                            'reference_key' => $referenceKey,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        Log::info('[Submit Review] Notification sent to inviter', [
                            'book_id' => $bookId,
                            'reviewer_id' => $laravelUser->id,
                            'inviter_id' => $inviter->id
                        ]);
                    }
                }
            }

            DB::commit();

            Log::info('[Submit Review] Review submitted successfully', [
                'book_id' => $bookId,
                'reviewer_id' => $laravelUser->id,
                'book_title' => $book->title
            ]);

            return redirect()->back()->with('success', 'Review berhasil dikirim');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[Submit Review] Error', [
                'book_id' => $bookId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Gagal mengirim review'], 500);
        }
    }
    /**
     * Kirim notifikasi pencairan dana berhasil ke Dosen pengaju
     */
    public static function sendBookPaymentSuccessNotification($bookId, $bookTitle, $dosenUserId)
    {
        try {
            Log::info('[Payment Success] Sending notification to dosen', [
                'book_id' => $bookId,
                'title' => $bookTitle,
                'dosen_user_id' => $dosenUserId
            ]);
            
            // Ambil Laravel user_id dari dosen
            $laravelUser = \App\Models\User::find($dosenUserId);
            
            if (!$laravelUser) {
                Log::warning('[Payment Success] Dosen user not found', ['user_id' => $dosenUserId]);
                return;
            }

            // Format pesan sesuai requirement
            $message = "Selamat! Dana insentif buku '{$bookTitle}' sudah cair.";
            $referenceKey = 'PAYMENT_SUCCESS_' . $bookId;

            // Cek apakah notifikasi sudah ada
            $notifExists = DB::table('notifications')
                ->where('user_id', $laravelUser->id)
                ->where('reference_key', $referenceKey)
                ->exists();

            if (!$notifExists) {
                DB::table('notifications')->insert([
                    'user_id' => $laravelUser->id,
                    'title' => 'Dana Insentif Buku Telah Cair',
                    'message' => $message,
                    'type' => 'Sukses',
                    'is_read' => false,
                    'reference_key' => $referenceKey,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                
                Log::info('[Payment Success] Notification created', [
                    'user_id' => $laravelUser->id,
                    'book_id' => $bookId,
                    'reference_key' => $referenceKey
                ]);
            } else {
                Log::info('[Payment Success] Notification already exists', [
                    'user_id' => $laravelUser->id,
                    'book_id' => $bookId
                ]);
            }

        } catch (\Exception $e) {
            Log::error('[Payment Success] Error sending notification', [
                'book_id' => $bookId,
                'dosen_user_id' => $dosenUserId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }


    /**
     * Kirim notifikasi pencairan dana buku ke SEMUA HRD.
     */
    public static function sendBookPaymentNotification($bookId, $bookTitle, $approvedAmount)
    {
        try {
            Log::info('[Static] Sending book payment notification to all HRD', [
                'book_id' => $bookId,
                'title' => $bookTitle,
                'amount' => $approvedAmount
            ]);
            
            // Ambil semua user yang memiliki akses HRD (case insensitive)
            $hrdUserIds = DB::table('m_hak_akses')
                ->where(function($query) {
                    $query->where('akses', 'like', '%HRD%')
                          ->orWhere('akses', 'like', '%Hrd%')
                          ->orWhere('akses', 'like', '%hrd%');
                })
                ->pluck('user_id')
                ->unique()
                ->toArray();
                
            Log::info('[Static] HRD users found from m_hak_akses', [
                'count' => count($hrdUserIds),
                'user_ids' => $hrdUserIds
            ]);
            
            $hrdUsers = \App\Models\User::whereIn('id', $hrdUserIds)->pluck('id')->toArray(); 

            if (empty($hrdUsers)) {
                Log::warning('[Static] No HRD users found in Laravel users table');
                return;
            }

            $nominal = 'Rp ' . number_format($approvedAmount, 0, ',', '.');
            $messageFormat = "Bayar: Segera cairkan {$nominal} untuk buku '{$bookTitle}'.";
            $notificationsToInsert = [];

            $referenceKey = 'PAYMENT_CHIEF_' . $bookId;

            foreach ($hrdUsers as $laravelUserId) {
                $notifExists = DB::table('notifications')
                    ->where('user_id', $laravelUserId)
                    ->where('reference_key', $referenceKey)
                    ->exists();

                if (!$notifExists) {
                    $notificationsToInsert[] = [
                        'user_id' => $laravelUserId,
                        'title' => 'Pembayaran Penghargaan Buku',
                        'message' => $messageFormat,
                        'type' => 'Peringatan',
                        'is_read' => false,
                        'reference_key' => $referenceKey,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                    
                    Log::info('[Static] Preparing notification for HRD user', [
                        'user_id' => $laravelUserId,
                        'reference_key' => $referenceKey
                    ]);
                } else {
                    Log::info('[Static] Notification already exists for HRD user', [
                        'user_id' => $laravelUserId,
                        'reference_key' => $referenceKey
                    ]);
                }
            }
            
            if (!empty($notificationsToInsert)) {
                DB::table('notifications')->insert($notificationsToInsert);
                Log::info('[Static] Batch payment notifications sent to HRD users', [
                    'book_id' => $bookId,
                    'count' => count($notificationsToInsert),
                    'users' => array_column($notificationsToInsert, 'user_id')
                ]);
            } else {
                Log::info('[Static] No new notifications to insert for HRD');
            }

        } catch (\Exception $e) {
            Log::error('[Static] Error in sendBookPaymentNotification', [
                'book_id' => $bookId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}