<?php

use App\Http\Controllers\App\HakAkses\HakAksesController;
use App\Http\Controllers\App\Home\HomeController;
use App\Http\Controllers\App\Penghargaan\PenghargaanBukuController;
use App\Http\Controllers\App\Todo\TodoController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['throttle:req-limit', 'handle.inertia'])->group(function () {
    // =========================================================================
    // 1. SSO Routes (Public)
    // =========================================================================
    Route::group(['prefix' => 'sso'], function () {
        Route::get('/callback', [AuthController::class, 'ssoCallback'])->name('sso.callback');
    });

    // =========================================================================
    // 2. Authentication Routes (Public/Guest)
    // =========================================================================
    Route::prefix('auth')->group(function () {
        Route::get('/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('/login-check', [AuthController::class, 'postLoginCheck'])->name('auth.login-check');
        Route::post('/login-post', [AuthController::class, 'postLogin'])->name('auth.login-post');
        Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/totp', [AuthController::class, 'totp'])->name('auth.totp');
        Route::post('/totp-post', [AuthController::class, 'postTotp'])->name('auth.totp-post');
    });

    // =========================================================================
    // 3. Protected Routes (Memerlukan Login)
    // =========================================================================
    Route::group(['middleware' => 'check.auth'], function () {
        
        // --- Dashboard Home ---
        Route::get('/', [HomeController::class, 'index'])->name('home');

        // --- Hak Akses Module ---
        Route::prefix('hak-akses')->group(function () {
            Route::get('/', [HakAksesController::class, 'index'])->name('hak-akses');
            Route::post('/change', [HakAksesController::class, 'postChange'])->name('hak-akses.change-post');
            Route::post('/delete', [HakAksesController::class, 'postDelete'])->name('hak-akses.delete-post');
            Route::post('/delete-selected', [HakAksesController::class, 'postDeleteSelected'])->name('hak-akses.delete-selected-post');
        });

        // --- Todo Module ---
        Route::prefix('todo')->group(function () {
            Route::get('/', [TodoController::class, 'index'])->name('todo');
            Route::post('/change', [TodoController::class, 'postChange'])->name('todo.change-post');
            Route::post('/delete', [TodoController::class, 'postDelete'])->name('todo.delete-post');
        });

        // ============================================================
        //    LPPM ROUTES (Modules)
        // ============================================================

        // UI Testing Route
        Route::get('/lppm/todo-ui', function () {
            return Inertia::render('app/lppm/todo-ui', [
                'pageName' => 'Todo UI Test'
            ]);
        })->name('lppm.todo-ui');

        // --- Registrasi Group (Dummy Closures) ---
        Route::prefix('registrasi')->group(function () {
            Route::get('/seminar', function () {
                return 'Halaman Registrasi Seminar (Segera Hadir)';
            })->name('registrasi.seminar');

            Route::get('/jurnal', function () {
                return 'Halaman Registrasi Jurnal (Segera Hadir)';
            })->name('registrasi.jurnal');
        });

        // --- Penghargaan Group ---
        Route::prefix('penghargaan')->group(function () {
            
            // 1. Penghargaan Buku (LENGKAP)
            
            // Halaman Utama (Card Info + Tabel)
            Route::get('/buku', [PenghargaanBukuController::class, 'index'])
                ->name('app.penghargaan.buku.index');
            
            // Langkah 1: Form Pengajuan Buku
            Route::get('/buku/ajukan', [PenghargaanBukuController::class, 'create'])
                ->name('app.penghargaan.buku.create');

            // Proses Simpan Langkah 1
            Route::post('/buku', [PenghargaanBukuController::class, 'store'])
                ->name('app.penghargaan.buku.store');

            // Langkah 2: Halaman Upload Dokumen (BARU)
            Route::get('/buku/upload/{id}', [PenghargaanBukuController::class, 'uploadDocs'])
                ->name('app.penghargaan.buku.upload');

            // Proses Simpan Langkah 2 (Final)
            Route::post('/buku/upload', [PenghargaanBukuController::class, 'storeUpload'])
                ->name('app.penghargaan.buku.store_upload');

            // 2. Penghargaan Lainnya (Masih Dummy)
            Route::get('/dosen', function () { return 'Penghargaan Dosen UI (Dummy)'; })->name('penghargaan.dosen');
            Route::get('/mahasiswa', function () { return 'Penghargaan Mahasiswa UI (Dummy)'; })->name('penghargaan.mahasiswa');
            Route::get('/penelitian', function () { return 'Penghargaan Penelitian UI (Dummy)'; })->name('penghargaan.penelitian');
        });
    });
});