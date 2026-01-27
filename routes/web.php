<?php

use App\Http\Controllers\Api\DosenController;
use App\Http\Controllers\App\HakAkses\HakAksesController;
use App\Http\Controllers\App\Home\HomeController;
use App\Http\Controllers\App\HRD\HRDController;
use App\Http\Controllers\App\Notifikasi\NotificationController;
use App\Http\Controllers\App\Penghargaan\AdminPenghargaanBukuController;
use App\Http\Controllers\App\Penghargaan\PenghargaanBukuController;
use App\Http\Controllers\App\Profile\ProfileController;
use App\Http\Controllers\App\RegisSemi\RegisSemiController;
use App\Http\Controllers\App\Todo\TodoController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route; // WAJIB
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
//use App\Http\Controllers\App\HakAkses\HakAksesController;
use App\Http\Controllers\App\Reviewer\Seminar\ReviewerSeminarController;


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

    // ------------------- PROTECTED -------------------
    Route::middleware('check.auth')->group(function () {

        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::get('/app/profile', [ProfileController::class, 'index'])
            ->name('app.profile');
        Route::post('/app/profile/update', [ProfileController::class, 'update'])
            ->name('app.profile.update');

        // ------------------- DOSEN Dashboard -------------------
        Route::middleware('role:Dosen')->group(function () {
            Route::get('/dosen/home', function () {
                return inertia('app/dosen/dosen-home-page', ['pageName' => 'Dashboard Dosen']);
            })->name('dosen.home');
        });

        Route::get('/hrd/home', [RegisSemiController::class, 'indexHRD'])->name('hrd.home');

        // ------------------- HAK AKSES -------------------
        Route::prefix('hak-akses')->group(function () {
            Route::get('/', [HakAksesController::class, 'index'])->name('hak-akses');
            Route::post('/change', [HakAksesController::class, 'postChange'])->name('hak-akses.change-post');
            Route::post('/delete', [HakAksesController::class, 'postDelete'])->name('hak-akses.delete-post');
            Route::post('/delete-selected', [HakAksesController::class, 'postDeleteSelected'])->name('hak-akses.delete-selected-post');
        });

        // ------------------- TODO -------------------
        // Route::prefix('todo')->group(function () {
        //     Route::get('/', [TodoController::class, 'index'])->name('todo');
        //     Route::post('/change', [TodoController::class, 'postChange'])->name('todo.change-post');
        //     Route::post('/delete', [TodoController::class, 'postDelete'])->name('todo.delete-post');
        // });

        Route::middleware(['auth', 'verified'])->prefix('app/admin/penghargaan')->name('app.admin.penghargaan.')->group(function () {

            // 🔥 PENTING: Daftarkan rute Admin Penghargaan Buku
            Route::get('buku', [AdminPenghargaanBukuController::class, 'index'])->name('buku.index');

            // Jika Anda memiliki rute detail:
            // Route::get('buku/{id}', [AdminPenghargaanBukuController::class, 'show'])->name('buku.detail');
        });

        // ------------------- REGIS SEMI (LPPM) -------------------
        Route::middleware('role:LppmKetua|Lppm Staff')->prefix('regis-semi')->name('regis-semi.')->group(function () {
            // Halaman utama
            Route::get('/', [RegisSemiController::class, 'index'])->name('index');
            Route::get('/buku-masuk', [RegisSemiController::class, 'indexBukuMasuk'])->name('indexx');

            Route::post('/change', [RegisSemiController::class, 'postChange'])->name('change');
            Route::post('/delete', [RegisSemiController::class, 'postDelete'])->name('delete');
            Route::post('/delete-selected', [RegisSemiController::class, 'postDeleteSelected'])->name('delete-selected');

            // Halaman detail
            Route::get('/{id}/detail', [RegisSemiController::class, 'show'])->name('show');
            Route::get('/{id}/detail-staff', [RegisSemiController::class, 'showStaff'])->name('show.staff');

            Route::get('/{id}/preview-pdf', [RegisSemiController::class, 'previewPdf'])->name('preview-pdf');
            Route::get('/{id}/download-pdf', [RegisSemiController::class, 'downloadPdf'])->name('download-pdf');

            // Tombol approve/reject
            Route::post('/{id}/approve', [RegisSemiController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [RegisSemiController::class, 'reject'])->name('reject');
            Route::post('/{id}/reject-staff', [RegisSemiController::class, 'rejectStaff'])->name('rejectStaff');

            // Halaman hasil dan undangan
            Route::get('/result', [RegisSemiController::class, 'result'])->name('result');
            Route::get('/{id}/review-results', [RegisSemiController::class, 'showReviewResults'])->name('review-results');

            Route::get('/{id}/invite', [RegisSemiController::class, 'invite'])->name('invite');
            Route::post('/{id}/invite', [RegisSemiController::class, 'storeInvite'])->name('store-invite');
            Route::get('/{id}/link-google-drive', [RegisSemiController::class, 'showInvite'])->name('link-google-drive');
        });

        // ------------------- PENGHARGAAN DOSEN -------------------
        Route::prefix('penghargaan')->middleware('role:Dosen')->group(function () {
            Route::get('/buku', [PenghargaanBukuController::class, 'index'])->name('app.penghargaan.buku.index');
            Route::get('/buku/ajukan', [PenghargaanBukuController::class, 'create'])->name('app.penghargaan.buku.create');
            Route::post('/buku', [PenghargaanBukuController::class, 'store'])->name('app.penghargaan.buku.store');
            Route::get('/buku/upload/{id}', [PenghargaanBukuController::class, 'uploadDocs'])->name('app.penghargaan.buku.upload');
            Route::post('/buku/upload/{id}', [PenghargaanBukuController::class, 'storeUpload'])->name('app.penghargaan.buku.store-upload');
            Route::get('/buku/{id}', [PenghargaanBukuController::class, 'show'])->name('app.penghargaan.buku.detail');
            Route::post('/buku/submit/{id}', [PenghargaanBukuController::class, 'submit'])->name('app.penghargaan.buku.submit');

            // PDF Routes
            Route::get('/buku/{id}/preview-pdf', [PenghargaanBukuController::class, 'previewPdf'])
                ->name('app.penghargaan.buku.preview-pdf');

            Route::get('/buku/{id}/download-pdf', [PenghargaanBukuController::class, 'downloadPdf'])
                ->name('app.penghargaan.buku.download-pdf');

            Route::get('/dosen', function () {
                return 'Penghargaan Dosen UI (Dummy)';
            })->name('penghargaan.dosen');
            Route::get('/penelitian', function () {
                return 'Penghargaan Penelitian UI (Dummy)';
            })->name('penghargaan.penelitian');
        });

        // ------------------- HRD -------------------
        Route::prefix('hrd')->name('hrd.')->group(function () {
            Route::get('/kita', [HRDController::class, 'index'])->name('kita.index');
            Route::post('/pencairan', [HRDController::class, 'storePencairan'])->name('pencairan');
        });

        // ------------------- PENGHARGAAN MAHASISWA -------------------
        Route::prefix('penghargaan')->middleware('role:Mahasiswa')->group(function () {
            Route::get('/mahasiswa', function () {
                return 'Penghargaan Mahasiswa UI (Dummy)';
            })->name('penghargaan.mahasiswa');
        });

        // ------------------- NOTIFIKASI -------------------
        Route::prefix('notifikasi')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
            Route::post('/cleanup', [NotificationController::class, 'cleanupNotifications'])->name('cleanup');
        });

        // ------------------- REVIEW SUBMISSION (REVIEWER) -------------------
        // 🔥 PERBAIKAN: Dipindahkan ke dalam check.auth middleware group
        Route::post('/review/submit/{bookId}', [NotificationController::class, 'submitReview'])
            ->name('review.submit');

        // ------------------- BOOK SUBMISSION PDF PREVIEW -------------------
        // Route untuk preview PDF dari book submission (untuk reviewer)
        Route::get('/book-submissions/{id}/preview-pdf', [RegisSemiController::class, 'previewPdf'])
            ->name('book-submissions.preview-pdf');
        Route::get('/book-submissions/{id}/download-pdf', [RegisSemiController::class, 'downloadPdf'])
            ->name('book-submissions.download-pdf');

    });
});

Route::middleware(['throttle:req-limit', 'handle.inertia'])->group(function () {

    // ----------------------------------------------------------------------
    // 1. SSO & AUTH ROUTES (Public)
    // ----------------------------------------------------------------------
    Route::prefix('sso')->group(function () {
        Route::get('/callback', [AuthController::class, 'ssoCallback'])->name('sso.callback');
    });

    // Route::middleware('auth:sanctum')->group(function () {
    //     Route::get('/hakakses/dosen', [DosenController::class, 'getDosenFromHakAkses']);
    // });

    Route::prefix('auth')->group(function () {
        Route::get('/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('/login-check', [AuthController::class, 'postLoginCheck'])->name('auth.login-check');
        Route::post('/login-post', [AuthController::class, 'postLogin'])->name('auth.login-post');
        Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/totp', [AuthController::class, 'totp'])->name('auth.totp');
        Route::post('/totp-post', [AuthController::class, 'postTotp'])->name('auth.totp-post');
    });

    // ----------------------------------------------------------------------
    // 2. PROTECTED ROUTES (Harus Login)
    // ----------------------------------------------------------------------
    Route::middleware('check.auth')->group(function () {

        // --- GENERAL (Semua Role bisa akses) ---
        Route::get('/', [HomeController::class, 'index'])->name('home');
        
        // ====================================================
        // 🔥 ROUTE PROFILE
        // Bisa diakses Dosen, Reviewer, LPPM, dll.
        // ====================================================
        Route::get('/profile/me', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile/me', [ProfileController::class, 'update'])->name('profile.update');

        // Notifikasi
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/cleanup', [NotificationController::class, 'cleanupNotifications']);

        // Todo List (Fitur Umum)
        Route::prefix('todo')->group(function () {
            Route::get('/', [TodoController::class, 'index'])->name('todo');
            Route::post('/change', [TodoController::class, 'postChange'])->name('todo.change-post');
            Route::post('/delete', [TodoController::class, 'postDelete'])->name('todo.delete-post');
        });

        // ------------------------------------------------------------------
        // ROLE: ADMIN (Mengurus Hak Akses)
        // ------------------------------------------------------------------
        Route::middleware('role:Admin')->prefix('hak-akses')->group(function () {
            Route::get('/', [HakAksesController::class, 'index'])->name('hak-akses');
            Route::post('/change', [HakAksesController::class, 'postChange'])->name('hak-akses.change-post');
            Route::post('/delete', [HakAksesController::class, 'postDelete'])->name('hak-akses.delete-post');
            Route::post('/delete-selected', [HakAksesController::class, 'postDeleteSelected'])->name('hak-akses.delete-selected-post');
        });

        // ------------------------------------------------------------------
        // ROLE: DOSEN (Pengajuan Seminar)
        // ------------------------------------------------------------------
        Route::middleware('role:Dosen')->prefix('dosen')->name('dosen.')->group(function () {
            
            // Dashboard Dosen
            Route::get('/home', function () {
                return Inertia::render('app/dosen/dosen-home-page', ['pageName' => 'Dashboard Dosen']);
            })->name('home');

            // List Seminar
            Route::get('/seminar', function () {
                return Inertia::render('app/dosen/seminar/seminar-page');
            })->name('seminar.index');

            // Form Registrasi Awal
            Route::get('/seminar/create', function () {
                return Inertia::render('app/dosen/seminar/registrasi-awal');
            })->name('seminar.create');

            // --- WORKFLOW PENGAJUAN (Step 1 sampai selesai) ---
            Route::prefix('seminar')->name('seminar.')->group(function () {
                // 1. DAFTAR SEMINAR (Pintu Masuk)
                Route::get('/', [SeminarController::class, 'index'])->name('index');

                // 2. REGISTRASI AWAL (Klik Tombol Tambah)
                Route::get('/create', [SeminarController::class, 'create'])->name('create');
                Route::post('/store', [SeminarController::class, 'store'])->name('store'); // Simpan awal

                // TAMBAHKAN RULE DELETE INI
                Route::delete('/{id}', [SeminarController::class, 'destroy'])->name('destroy');
                
                // 🔥 ROUTE BARU: Aksi Persetujuan Akhir (ACC Final) 🔥
                Route::post('/{id}/acc-final', [ReviewerSeminarController::class, 'accSeminar'])
                    ->name('acc_final');

                // ====================================================
                // 🔥 RUTE BARU: DOWNLOAD FORM 3
                // ====================================================
                Route::get('/{id}/download-form-3', [SeminarController::class, 'downloadForm3'])->name('download-form-3');
                
                // 3. STEP 1: DATA DETAIL (Edit Data yang sudah dibuat)
                Route::get('/{id}/step-1', [SeminarController::class, 'editStep1'])->name('step1');
                Route::post('/{id}/step-1', [SeminarController::class, 'updateStep1'])->name('step1.update');

                // 4. STEP 2: UPLOAD PAPER
                Route::get('/{id}/step-2', [SeminarController::class, 'createStep2'])->name('step2');
                Route::post('/{id}/step-2', [SeminarController::class, 'storeStep2'])->name('step2.store');


                // Route Download Form 1 (Hasil Review)
                // URL nanti jadi: /dosen/seminar/review/{reviewId}/download-form-1
                Route::get('/review/{reviewId}/download-form-1', [SeminarController::class, 'downloadForm1'])
                    ->name('download_form1');

                // 🔥 ROUTE DOWNLOAD FORM 2 (Surat Persetujuan Reviewer)
                Route::get('/review/{reviewId}/download-form-2', [ReviewerSeminarController::class, 'downloadForm2'])
                    ->name('download_form2');

                // ====================================================
                // ✅ ROUTE REVISI & ALUR DINAMIS (DENGAN ID)
                // ====================================================
                
                // 1. Halaman Form Perbaikan (Melihat catatan reviewer)
                Route::get('/{id}/perbaikan', [SeminarController::class, 'showPerbaikanPage'])
                    ->name('perbaikan');

                // 2. Action Kirim File Revisi & Update Status
                Route::post('/{id}/submit-perbaikan', [SeminarController::class, 'submitPerbaikan'])
                    ->name('submit_perbaikan');

                // 3. Step 3: Handler Pintar (Menentukan arah: Loading / Perbaikan / Lanjut)
                Route::get('/{id}/step-3', [SeminarController::class, 'step3Handler'])
                    ->name('step3');
                
                Route::get('/{id}/step-3-hasil', [SeminarController::class, 'showHasilReviewPage'])
                    ->name('step3.hasil');

                // ----------------------------------------------------
                // PERBAIKAN: SEMUA STEP BERIKUTNYA WAJIB PAKAI {id}
                // Agar controller tahu data mana yang sedang diproses
                // ----------------------------------------------------

                // Step 4: Submit Artefak
                // 🔥 HAPUS/GANTI baris yang pakai function($id) {...}
                // GANTI DENGAN INI:
                Route::get('/{id}/step-4', [SeminarController::class, 'createStep4'])
                    ->name('step4');
                
                Route::post('/{id}/step-4', [SeminarController::class, 'storeStep4'])
                    ->name('step4.store');

                // Step 5: Pencairan Dana
                // 🔥 HAPUS Closure function($id) {...}
                // GANTI DENGAN:
                Route::get('/{id}/step-5', [SeminarController::class, 'createStep5'])
                    ->name('step5');

                // Step 6: Mode Seminar & Surat Tugas
                Route::get('/{id}/step-6', [SeminarController::class, 'createStep6'])
                    ->name('step6');
                
                Route::post('/{id}/step-6', [SeminarController::class, 'storeStep6'])
                    ->name('step6.store');

                // Finish
                Route::get('/{id}/finish', [SeminarController::class, 'finish'])
                    ->name('finish');
            });
        });

        // ✅ TAMBAHAN: DUMMY ROUTES UNTUK MENU DOSEN YANG LAIN
        // Agar tidak error saat dipanggil di sidebar
        Route::get('/registrasi/jurnal', function() { return "Halaman Registrasi Jurnal (Dummy)"; })->name('registrasi.jurnal');
        
        Route::prefix('penghargaan')->name('app.penghargaan.buku.')->group(function () {
             Route::get('/buku', [PenghargaanBukuController::class, 'index'])->name('index');
             Route::get('/buku/ajukan', [PenghargaanBukuController::class, 'create'])->name('create');
             Route::post('/buku', [PenghargaanBukuController::class, 'store'])->name('store');
             Route::get('/buku/upload/{id}', [PenghargaanBukuController::class, 'uploadDocs'])->name('upload');
             Route::post('/buku/upload/{id}', [PenghargaanBukuController::class, 'storeUpload'])->name('store-upload');
             Route::get('/buku/{id}', [PenghargaanBukuController::class, 'show'])->name('detail');
             Route::post('/buku/submit/{id}', [PenghargaanBukuController::class, 'submit'])->name('submit');
             Route::get('/buku/{id}/preview-pdf', [PenghargaanBukuController::class, 'previewPdf'])->name('preview-pdf');
             Route::get('/buku/{id}/download-pdf', [PenghargaanBukuController::class, 'downloadPdf'])->name('download-pdf');
        });


        // ------------------------------------------------------------------
        // ROLE: KPRODI (Verifikasi Akademik)
        // ------------------------------------------------------------------
        // Pastikan Anda sudah import controller di atas: 
        // use App\Http\Controllers\App\Kprodi\KprodiController;

        Route::middleware('role:Kprodi')->prefix('kprodi')->name('kprodi.')->group(function () {
            
            // Dashboard (Bisa diarahkan ke verifikasi jika dashboard belum ada isinya)
            Route::get('/dashboard', function () {
                return redirect()->route('kprodi.verifikasi.index');
            })->name('home');

            // 1. Daftar Verifikasi
            Route::get('/verifikasi', [\App\Http\Controllers\App\Kprodi\KprodiController::class, 'index'])
                ->name('verifikasi.index');

            // 2. Detail Pengajuan
            Route::get('/verifikasi/{id}', [\App\Http\Controllers\App\Kprodi\KprodiController::class, 'show'])
                ->name('verifikasi.detail');

            // 3. Action Approve
            Route::post('/verifikasi/{id}/approve', [\App\Http\Controllers\App\Kprodi\KprodiController::class, 'approve'])
                ->name('verifikasi.approve');

            // 4. Halaman Tolak (View)
            Route::get('/verifikasi/{id}/tolak', function ($id) {
                return Inertia::render('app/kprodi/tolak-verifikasi', ['id' => $id]);
            })->name('verifikasi.tolak');

            // 5. Action Reject (Post)
            Route::post('/verifikasi/{id}/tolak', [\App\Http\Controllers\App\Kprodi\KprodiController::class, 'reject'])
                ->name('verifikasi.store-tolak');
        });


        // ------------------------------------------------------------------
        // ROLE: REVIEWER (Review Paper)
        // ------------------------------------------------------------------
        Route::middleware('role:Reviewer')->prefix('reviewer')->name('reviewer.')->group(function () {
            
            Route::get('/home', function () {
                return Inertia::render('app/reviewer/reviewer-home-page', [
                    'pageName' => 'Dashboard Reviewer'
                ]);
            })->name('home');

            // Seminar Routes
            Route::prefix('seminar')->name('seminar.')->group(function () {
                
                // 1. Halaman List Seminar Masuk
                Route::get('/masuk', [ReviewerSeminarController::class, 'index'])->name('masuk');

                // 2. Aksi Terima / Tolak / Selesai (Update Status)
                Route::patch('/{id}/update-status', [ReviewerSeminarController::class, 'updateStatus'])
                    ->name('update_status');

                // 🔥 ROUTE BARU: Aksi Persetujuan Akhir (ACC Final) 🔥
                Route::post('/{id}/acc-final', [ReviewerSeminarController::class, 'accSeminar'])
                    ->name('acc_final');

                // 3. Halaman Form Review (Ubah ke Controller agar data review & seminar terkirim)
                Route::get('/review/{id}', [ReviewerSeminarController::class, 'review'])
                    ->name('review');

                // 4. ✅ ROUTE BARU: SIMPAN ISI REVIEW (Nilai & Catatan)
                Route::put('/{id}/review-content', [ReviewerSeminarController::class, 'updateReviewContent'])
                    ->name('update_review_content');

                // Arahkan ke Controller method 'disetujui'
                Route::get('/disetujui', [ReviewerSeminarController::class, 'disetujui'])
                    ->name('disetujui');

                // Route Download Paper
                Route::get('/{id}/download', [ReviewerSeminarController::class, 'downloadPaper'])
                    ->name('download');
            });



            // JURNAL ROUTES (Dummy / Placeholder)
            Route::prefix('jurnal')->name('jurnal.')->group(function () {
                Route::get('/masuk', function () { 
                    return Inertia::render('app/reviewer/seminar/seminar-masuk-page'); 
                })->name('masuk');

                Route::get('/disetujui', function () {
                    return Inertia::render('app/reviewer/seminar/seminar-disetujui-page');
                })->name('disetujui');
            });
            
        });


        // ------------------------------------------------------------------
        // ROLE: LPPM KETUA (Approval Akhir)
        // ------------------------------------------------------------------
        Route::middleware('role:LppmKetua')->prefix('lppm/ketua')->name('lppm.ketua.')->group(function () {
            
            // Dashboard (Redirect ke pengajuan dana untuk sementara)
            Route::get('/home', function () {
                return redirect()->route('lppm.ketua.pengajuan-dana');
            })->name('home');

            // 1. Daftar Pengajuan
            Route::get('/pengajuan-dana', [\App\Http\Controllers\App\Lppm\LppmKetuaController::class, 'index'])
                ->name('pengajuan-dana');

            // 2. Detail Verifikasi
            Route::get('/pengajuan-dana/{id}/konfirmasi', [\App\Http\Controllers\App\Lppm\LppmKetuaController::class, 'show'])
                ->name('pengajuan-dana.konfirmasi');

            // 3. Halaman & Proses Upload Surat (Approve)
            Route::get('/pengajuan-dana/{id}/upload', [\App\Http\Controllers\App\Lppm\LppmKetuaController::class, 'createUpload'])
                ->name('pengajuan-dana.upload');
            
            Route::post('/pengajuan-dana/{id}/upload', [\App\Http\Controllers\App\Lppm\LppmKetuaController::class, 'storeUpload'])
                ->name('pengajuan-dana.store-upload');

            // 4. Halaman & Proses Tolak
            Route::get('/pengajuan-dana/{id}/tolak', [\App\Http\Controllers\App\Lppm\LppmKetuaController::class, 'createReject'])
                ->name('pengajuan-dana.tolak');

            Route::post('/pengajuan-dana/{id}/tolak', [\App\Http\Controllers\App\Lppm\LppmKetuaController::class, 'storeReject'])
                ->name('pengajuan-dana.store-tolak');

                // ROUTE DOWNLOAD FORM 3
            Route::get('/pengajuan-dana/{id}/download-f3', [\App\Http\Controllers\App\Lppm\LppmKetuaController::class, 'downloadForm3'])
                ->name('pengajuan-dana.download-f3');
        });

        // ------------------------------------------------------------------
        // ROLE: LPPM ANGGOTA (Verifikasi Dokumen)
        // ------------------------------------------------------------------
        Route::middleware('role:LppmAnggota')->prefix('lppm/anggota')->name('lppm.anggota.')->group(function () {
            Route::get('/home', function () {
                return Inertia::render('app/lppm/anggota/anggota-home-page');
            })->name('home');

            Route::get('/pengajuan-dana', function () {
                return Inertia::render('app/lppm/anggota/pengajuan-dana-page');
            })->name('pengajuan-dana');

            Route::get('/pengajuan-dana/{id}', function ($id) {
                return Inertia::render('app/lppm/anggota/pengajuan-dana-detail-page', ['id' => $id]);
            })->name('pengajuan-dana.detail');
            
        });


        // ------------------------------------------------------------------
        // ROLE: KEUANGAN (Pencairan Dana)
        // ------------------------------------------------------------------
        // Pastikan use controller: use App\Http\Controllers\App\Keuangan\KeuanganController;

        Route::middleware('role:Keuangan')->prefix('keuangan')->name('keuangan.')->group(function () {
            
            // Dashboard
            Route::get('/home', function () {
                return redirect()->route('keuangan.pembayaran');
            })->name('home');

            // 1. Daftar Pembayaran
            Route::get('/pembayaran', [\App\Http\Controllers\App\Keuangan\KeuanganController::class, 'index'])
                ->name('pembayaran');

            // 2. Detail Pembayaran
            Route::get('/pembayaran/{id}', [\App\Http\Controllers\App\Keuangan\KeuanganController::class, 'show'])
                ->name('pembayaran.detail');

            // 3. Upload Bukti Transfer
            Route::get('/pembayaran/{id}/upload', [\App\Http\Controllers\App\Keuangan\KeuanganController::class, 'createUpload'])
                ->name('pembayaran.upload');
            
            // 4. Proses Simpan
            Route::post('/pembayaran/{id}/upload', [\App\Http\Controllers\App\Keuangan\KeuanganController::class, 'storeUpload'])
                ->name('pembayaran.store-upload');
        });


        // ------------------------------------------------------------------
        // ROLE: HRD (Surat TugasSurat Tugas Onsite)
        // ------------------------------------------------------------------
        Route::middleware('role:Hrd')->prefix('hrd')->name('hrd.')->group(function () {
            
            // Dashboard HRD
            Route::get('/dashboard', function () {
                return redirect()->route('hrd.surat-tugas.index');
            })->name('home');

            // 1. List Request Surat Tugas
            Route::get('/surat-tugas', [\App\Http\Controllers\App\HRD\HRDController::class, 'index'])
                ->name('surat-tugas.index');

            // 2. Halaman Upload
            Route::get('/surat-tugas/{id}/upload', [\App\Http\Controllers\App\HRD\HRDController::class, 'createUpload'])
                ->name('surat-tugas.upload');

            // 3. Proses Simpan
            Route::post('/surat-tugas/{id}/upload', [\App\Http\Controllers\App\HRD\HRDController::class, 'storeUpload'])
                ->name('surat-tugas.store');
        });

    }); // End Middleware Check Auth
});