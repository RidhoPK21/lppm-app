<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\App\Home\HomeController;
use App\Http\Controllers\App\HakAkses\HakAksesController;
use App\Http\Controllers\App\Todo\TodoController;
use App\Http\Controllers\App\RegisSemi\RegisSemiController;
use App\Http\Controllers\App\Penghargaan\PenghargaanBukuController;
use Inertia\Inertia;

Route::middleware(['throttle:req-limit', 'handle.inertia'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | SSO ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('sso')->group(function () {
        Route::get('/callback', [AuthController::class, 'ssoCallback'])->name('sso.callback');
    });

    /*
    |--------------------------------------------------------------------------
    | AUTH ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::get('/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('/login-check', [AuthController::class, 'postLoginCheck'])->name('auth.login-check');
        Route::post('/login-post', [AuthController::class, 'postLogin'])->name('auth.login-post');

        Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::get('/totp', [AuthController::class, 'totp'])->name('auth.totp');
        Route::post('/totp-post', [AuthController::class, 'postTotp'])->name('auth.totp-post');
    });

    /*
    |--------------------------------------------------------------------------
    | PROTECTED ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.auth')->group(function () {

        // HOME
        Route::get('/', [HomeController::class, 'index'])->name('home');

        // DOSEN
        Route::get('/dosen/home', function () {
            return inertia('app/dosen/dosen-home-page', [
                'pageName' => 'Dashboard Dosen',
            ]);
        })->name('dosen.home');

        // HAK AKSES
        Route::prefix('hak-akses')->group(function () {
            Route::get('/', [HakAksesController::class, 'index'])->name('hak-akses');
            Route::post('/change', [HakAksesController::class, 'postChange'])->name('hak-akses.change-post');
            Route::post('/delete', [HakAksesController::class, 'postDelete'])->name('hak-akses.delete-post');
            Route::post('/delete-selected', [HakAksesController::class, 'postDeleteSelected'])->name('hak-akses.delete-selected-post');
        });



        // TODO
        Route::prefix('todo')->group(function () {
            Route::get('/', [TodoController::class, 'index'])->name('todo');
            Route::post('/change', [TodoController::class, 'postChange'])->name('todo.change-post');
            Route::post('/delete', [TodoController::class, 'postDelete'])->name('todo.delete-post');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | LPPM - REGISTRASI SEMINAR / JURNAL
    |--------------------------------------------------------------------------
    */
 // ...
Route::get('/app/regis-semi/{id}/link-google-drive', [RegisSemiController::class, 'showInvite'])
     ->name('regis-semi.invite'); // <-- PASTIKAN NAMA INI SAMA
// ...
Route::get('/{id}/invite', [RegisSemiController::class, 'invite'])->name('invite');
// Rute untuk Detail Buku
Route::get('/app/regis-semi/{id}/detail', [RegisSemiController::class, 'show'])->name('regis-semi.detail');
Route::get('/{id}/result', [RegisSemiController::class, 'result'])->name('regis-semi.result');

Route::prefix('regis-semi')->name('regis-semi.')->group(function () {
            Route::get('/', [RegisSemiController::class, 'index'])->name('index');
            Route::post('/change', [RegisSemiController::class, 'postChange'])->name('change');
            Route::post('/delete', [RegisSemiController::class, 'postDelete'])->name('delete');
            Route::post('/delete-selected', [RegisSemiController::class, 'postDeleteSelected'])->name('delete-selected');
        });
   

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

    Route::get('/notifikasi-dummy', function () {
            return Inertia::render('app/notifikasi/page');
        })->name('notifications.index');
  
});