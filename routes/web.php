<?php

use Illuminate\Support\Facades\Route;

// --- CONTROLLERS AUTH & SYSTEM ---
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\App\Home\HomeController;
use App\Http\Controllers\App\Profile\ProfileController;
use App\Http\Controllers\App\HakAkses\HakAksesController;
use App\Http\Controllers\App\Notifikasi\NotificationController;
use App\Http\Controllers\Api\DosenController;

// --- CONTROLLERS FITUR LAMA (Regis Semi & Penghargaan Buku) ---
use App\Http\Controllers\App\RegisSemi\RegisSemiController;
use App\Http\Controllers\App\Penghargaan\PenghargaanBukuController;
use App\Http\Controllers\App\Penghargaan\AdminPenghargaanBukuController;
use App\Http\Controllers\App\HRD\HRDController; // HRD Lama

// --- CONTROLLERS FITUR BARU (SEMINAR WORKFLOW) ---
use App\Http\Controllers\App\Dosen\Seminar\SeminarController;
use App\Http\Controllers\App\Reviewer\Seminar\ReviewerSeminarController;
use App\Http\Controllers\App\Kprodi\KprodiController;
use App\Http\Controllers\App\Keuangan\KeuanganController;
use App\Http\Controllers\App\Lppm\LppmKetuaController;

// ==================================================================================
//                                  ROUTE DEFINITION
// ==================================================================================

Route::middleware(['throttle:req-limit', 'handle.inertia'])->group(function () {

    // ------------------- SSO -------------------
    Route::prefix('sso')->group(function () {
        Route::get('/callback', [AuthController::class, 'ssoCallback'])->name('sso.callback');
    });

    Route::middleware('auth:sanctum')->group(function () {
        // API untuk mendapatkan dosen dari tabel HakAkses
        Route::get('/hakakses/dosen', [DosenController::class, 'getDosenFromHakAkses']);
    });

    // ------------------- AUTH -------------------
    Route::prefix('auth')->group(function () {
        Route::get('/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('/login-check', [AuthController::class, 'postLoginCheck'])->name('auth.login-check');
        Route::post('/login-post', [AuthController::class, 'postLogin'])->name('auth.login-post');
        Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/totp', [AuthController::class, 'totp'])->name('auth.totp');
        Route::post('/totp-post', [AuthController::class, 'postTotp'])->name('auth.totp-post');
    });

    // ------------------- PROTECTED ROUTES -------------------
    Route::middleware('check.auth')->group(function () {

        // --- DASHBOARD UMUM ---
        Route::get('/', [HomeController::class, 'index'])->name('home');

        // --- PROFILE ---
        Route::get('/app/profile', [ProfileController::class, 'index'])->name('app.profile');
        Route::post('/app/profile/update', [ProfileController::class, 'update'])->name('app.profile.update');

        // --- HAK AKSES (ADMIN) ---
        Route::prefix('hak-akses')->name('hak-akses')->group(function () { // Perbaikan name prefix
            Route::get('/', [HakAksesController::class, 'index']); // name: hak-akses
            Route::post('/change', [HakAksesController::class, 'postChange'])->name('.change-post');
            Route::post('/delete', [HakAksesController::class, 'postDelete'])->name('.delete-post');
            Route::post('/delete-selected', [HakAksesController::class, 'postDeleteSelected'])->name('.delete-selected-post');
        });

        // --- NOTIFIKASI ---
        Route::prefix('notifikasi')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
            Route::post('/cleanup', [NotificationController::class, 'cleanupNotifications'])->name('cleanup');
            
            // Route submit review dari notifikasi (Logic lama)
            Route::post('/review/submit/{bookId}', [NotificationController::class, 'submitReview'])->name('review.submit');
        });


        // ######################################################################
        //                       BAGIAN 1: FITUR LAMA
        //          (Penghargaan Buku, Regis Semi, HRD Existing)
        // ######################################################################

        // 1.1 ADMIN PENGHARGAAN BUKU
        Route::middleware(['auth', 'verified'])->prefix('app/admin/penghargaan')->name('app.admin.penghargaan.')->group(function () {
            Route::get('buku', [AdminPenghargaanBukuController::class, 'index'])->name('buku.index');
        });

        // 1.2 REGIS SEMI (LPPM Lama)
        Route::middleware('role:LppmKetua|Lppm Staff')->prefix('regis-semi')->name('regis-semi.')->group(function () {
            Route::get('/', [RegisSemiController::class, 'index'])->name('index');
            Route::get('/buku-masuk', [RegisSemiController::class, 'indexBukuMasuk'])->name('indexx');
            Route::post('/change', [RegisSemiController::class, 'postChange'])->name('change');
            Route::post('/delete', [RegisSemiController::class, 'postDelete'])->name('delete');
            Route::post('/delete-selected', [RegisSemiController::class, 'postDeleteSelected'])->name('delete-selected');
            Route::get('/{id}/detail', [RegisSemiController::class, 'show'])->name('show');
            Route::get('/{id}/detail-staff', [RegisSemiController::class, 'showStaff'])->name('show.staff');
            Route::get('/{id}/preview-pdf', [RegisSemiController::class, 'previewPdf'])->name('preview-pdf');
            Route::get('/{id}/download-pdf', [RegisSemiController::class, 'downloadPdf'])->name('download-pdf');
            Route::post('/{id}/approve', [RegisSemiController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [RegisSemiController::class, 'reject'])->name('reject');
            Route::post('/{id}/reject-staff', [RegisSemiController::class, 'rejectStaff'])->name('rejectStaff');
            Route::get('/result', [RegisSemiController::class, 'result'])->name('result');
            Route::get('/{id}/review-results', [RegisSemiController::class, 'showReviewResults'])->name('review-results');
            Route::get('/{id}/invite', [RegisSemiController::class, 'invite'])->name('invite');
            Route::post('/{id}/invite', [RegisSemiController::class, 'storeInvite'])->name('store-invite');
            Route::get('/{id}/link-google-drive', [RegisSemiController::class, 'showInvite'])->name('link-google-drive');
            // Route preview PDF tambahan
            Route::get('/book-submissions/{id}/preview-pdf', [RegisSemiController::class, 'previewPdf'])->name('book-submissions.preview-pdf');
            Route::get('/book-submissions/{id}/download-pdf', [RegisSemiController::class, 'downloadPdf'])->name('book-submissions.download-pdf');
        });

        // 1.3 PENGHARGAAN BUKU (Dosen)
        Route::prefix('penghargaan')->middleware('role:Dosen')->group(function () {
            Route::get('/buku', [PenghargaanBukuController::class, 'index'])->name('app.penghargaan.buku.index');
            Route::get('/buku/ajukan', [PenghargaanBukuController::class, 'create'])->name('app.penghargaan.buku.create');
            Route::post('/buku', [PenghargaanBukuController::class, 'store'])->name('app.penghargaan.buku.store');
            Route::get('/buku/upload/{id}', [PenghargaanBukuController::class, 'uploadDocs'])->name('app.penghargaan.buku.upload');
            Route::post('/buku/upload/{id}', [PenghargaanBukuController::class, 'storeUpload'])->name('app.penghargaan.buku.store-upload');
            Route::get('/buku/{id}', [PenghargaanBukuController::class, 'show'])->name('app.penghargaan.buku.detail');
            Route::post('/buku/submit/{id}', [PenghargaanBukuController::class, 'submit'])->name('app.penghargaan.buku.submit');
            Route::get('/buku/{id}/preview-pdf', [PenghargaanBukuController::class, 'previewPdf'])->name('app.penghargaan.buku.preview-pdf');
            Route::get('/buku/{id}/download-pdf', [PenghargaanBukuController::class, 'downloadPdf'])->name('app.penghargaan.buku.download-pdf');
            
            // Dummy Routes
            Route::get('/dosen', fn() => 'Penghargaan Dosen UI (Dummy)')->name('penghargaan.dosen');
            Route::get('/penelitian', fn() => 'Penghargaan Penelitian UI (Dummy)')->name('penghargaan.penelitian');
        });

        // 1.4 HRD Existing (Kita & Pencairan)
        Route::prefix('hrd')->name('hrd.')->group(function () {
            Route::get('/home', [RegisSemiController::class, 'indexHRD'])->name('home'); // Route HRD Home dari RegisSemi
            Route::get('/kita', [HRDController::class, 'index'])->name('kita.index');
            Route::post('/pencairan', [HRDController::class, 'storePencairan'])->name('pencairan');
        });

        // 1.5 PENGHARGAAN MAHASISWA (Dummy)
        Route::prefix('penghargaan')->middleware('role:Mahasiswa')->group(function () {
            Route::get('/mahasiswa', fn() => 'Penghargaan Mahasiswa UI (Dummy)')->name('penghargaan.mahasiswa');
        });


        // ######################################################################
        //                       BAGIAN 2: FITUR BARU (SEMINAR)
        //       (Ditransplantasi dari pormanmarr/lppm-gabung-app)
        // ######################################################################

        // 2.1 DOSEN - ALUR SEMINAR (STEP 1 - 6)
        Route::middleware('role:Dosen')->prefix('dosen')->name('dosen.')->group(function () {
            // Dashboard Dosen (Inertia)
            Route::get('/home', function () {
                return inertia('app/dosen/dosen-home-page', ['pageName' => 'Dashboard Dosen']);
            })->name('home');

            // --- SEMINAR WORKFLOW ---
            Route::prefix('seminar')->name('seminar.')->group(function () {
                // List & Create
                Route::get('/', [SeminarController::class, 'index'])->name('index');
                Route::get('/registrasi-awal', [SeminarController::class, 'create'])->name('create');
                Route::post('/registrasi-awal', [SeminarController::class, 'store'])->name('store');
                Route::delete('/{id}', [SeminarController::class, 'destroy'])->name('destroy');

                // Step 1: Data
                Route::get('/{id}/step-1', [SeminarController::class, 'editStep1'])->name('step1');
                Route::post('/{id}/step-1', [SeminarController::class, 'updateStep1'])->name('step1.update');

                // Step 2: Paper
                Route::get('/{id}/step-2', [SeminarController::class, 'createStep2'])->name('step2');
                Route::post('/{id}/step-2', [SeminarController::class, 'storeStep2'])->name('step2.store');

                // Step 3: Finalisasi / Hasil Review / Perbaikan
                Route::get('/{id}/step-3', [SeminarController::class, 'indexStep3'])->name('step3'); // Handler logic
                Route::post('/{id}/step-3/submit-revision', [SeminarController::class, 'submitRevision'])->name('step3.submit-revision');

                // Step 4: Artefak
                Route::get('/{id}/step-4', [SeminarController::class, 'createStep4'])->name('step4');
                Route::post('/{id}/step-4', [SeminarController::class, 'storeStep4'])->name('step4.store');

                // Step 5: Pencairan (Upload Bukti Bayar/Tiket)
                Route::get('/{id}/step-5', [SeminarController::class, 'createStep5'])->name('step5');
                Route::post('/{id}/step-5', [SeminarController::class, 'storeStep5'])->name('step5.store');

                // Step 6: Mode Presentasi & Surat Tugas
                Route::get('/{id}/step-6', [SeminarController::class, 'createStep6'])->name('step6');
                Route::post('/{id}/step-6/onsite', [SeminarController::class, 'storeStep6Onsite'])->name('step6.onsite');
                Route::post('/{id}/step-6/online', [SeminarController::class, 'storeStep6Online'])->name('step6.online');

                // Finish
                Route::get('/{id}/finish', [SeminarController::class, 'showFinish'])->name('finish');
            });
        });

        // 2.2 REVIEWER - ALUR REVIEW SEMINAR
        Route::middleware('role:Reviewer')->prefix('reviewer')->name('reviewer.')->group(function () {
            Route::get('/home', [ReviewerSeminarController::class, 'indexHome'])->name('home'); // Dashboard Reviewer
            
            Route::prefix('seminar')->name('seminar.')->group(function () {
                Route::get('/masuk', [ReviewerSeminarController::class, 'indexMasuk'])->name('masuk');
                Route::get('/disetujui', [ReviewerSeminarController::class, 'indexDisetujui'])->name('disetujui');
                
                // Proses Review
                Route::get('/review/{id}', [ReviewerSeminarController::class, 'showReviewForm'])->name('review.form');
                Route::put('/review/{id}', [ReviewerSeminarController::class, 'updateReview'])->name('review.update');
                
                // Download Paper
                Route::get('/download-paper/{id}', [ReviewerSeminarController::class, 'downloadPaper'])->name('download.paper');
            });
        });

        // 2.3 KPRODI - VERIFIKASI AKADEMIK
        Route::middleware('role:Kprodi')->prefix('kprodi')->name('kprodi.')->group(function () {
            Route::get('/home', [KprodiController::class, 'index'])->name('home');
            
            Route::get('/verifikasi', [KprodiController::class, 'indexVerifikasi'])->name('verifikasi.index');
            Route::get('/verifikasi/{id}', [KprodiController::class, 'showVerifikasi'])->name('verifikasi.show');
            Route::post('/verifikasi/{id}/approve', [KprodiController::class, 'approve'])->name('verifikasi.approve');
            Route::post('/verifikasi/{id}/reject', [KprodiController::class, 'reject'])->name('verifikasi.reject');
        });

        // 2.4 KEUANGAN - VERIFIKASI PEMBAYARAN
        Route::middleware('role:Keuangan')->prefix('keuangan')->name('keuangan.')->group(function () {
            Route::get('/home', [KeuanganController::class, 'index'])->name('home');
            
            Route::get('/pembayaran', [KeuanganController::class, 'indexPembayaran'])->name('pembayaran.index');
            Route::get('/pembayaran/{id}', [KeuanganController::class, 'showPembayaran'])->name('pembayaran.show');
            Route::post('/pembayaran/{id}/confirm', [KeuanganController::class, 'confirmPembayaran'])->name('pembayaran.confirm');
        });

        // 2.5 LPPM KETUA (Workflow Seminar Baru)
        // Note: LPPM Ketua juga punya akses ke 'regis-semi' (fitur lama) di atas.
        Route::middleware('role:LppmKetua')->prefix('lppm/ketua')->name('lppm.ketua.')->group(function () {
            Route::get('/home', [LppmKetuaController::class, 'index'])->name('home');
            
            // Pengajuan Dana (Seminar)
            Route::get('/pengajuan-dana', [LppmKetuaController::class, 'indexPengajuanDana'])->name('pengajuan-dana.index');
            Route::get('/pengajuan-dana/{id}', [LppmKetuaController::class, 'showPengajuanDana'])->name('pengajuan-dana.show');
            Route::post('/pengajuan-dana/{id}/approve', [LppmKetuaController::class, 'approvePengajuanDana'])->name('pengajuan-dana.approve');
            Route::post('/pengajuan-dana/{id}/reject', [LppmKetuaController::class, 'rejectPengajuanDana'])->name('pengajuan-dana.reject');
            
            // Upload SK (Jika ada di workflow Ketua)
            Route::post('/upload-sk/{id}', [LppmKetuaController::class, 'uploadSK'])->name('upload-sk');
        });

        // 2.6 HRD - SURAT TUGAS (Workflow Seminar Baru)
        Route::middleware('role:HRD')->prefix('hrd-seminar')->name('hrd.seminar.')->group(function () {
            // Kita pisahkan prefix biar gak bentrok sama HRD fitur lama
            Route::get('/', [HRDController::class, 'indexSeminar'])->name('index'); 
            Route::get('/{id}/upload-surat', [HRDController::class, 'showUploadSurat'])->name('upload.show');
            Route::post('/{id}/upload-surat', [HRDController::class, 'storeUploadSurat'])->name('upload.store');
        });

    }); // End Middleware check.auth
});