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
    | PROTECTED ROUTES (LOGIN REQUIRED)
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.auth')->group(function () {

        // HOME DASHBOARD
        Route::get('/', [HomeController::class, 'index'])->name('home');

        // DOSEN SPECIFIC
        Route::get('/dosen/home', function () {
            return inertia('app/dosen/dosen-home-page', [
                'pageName' => 'Dashboard Dosen',
            ]);
        })->name('dosen.home');

        // MANAJEMEN HAK AKSES
        Route::prefix('hak-akses')->group(function () {
            Route::get('/', [HakAksesController::class, 'index'])->name('hak-akses');
            Route::post('/change', [HakAksesController::class, 'postChange'])->name('hak-akses.change-post');
            Route::post('/delete', [HakAksesController::class, 'postDelete'])->name('hak-akses.delete-post');
            Route::post('/delete-selected', [HakAksesController::class, 'postDeleteSelected'])->name('hak-akses.delete-selected-post');
        });

        // TODO LIST
        Route::prefix('todo')->group(function () {
            Route::get('/', [TodoController::class, 'index'])->name('todo');
            Route::post('/change', [TodoController::class, 'postChange'])->name('todo.change-post');
            Route::post('/delete', [TodoController::class, 'postDelete'])->name('todo.delete-post');
        });

        /*
        |--------------------------------------------------------------------------
        | LPPM APPS
        |--------------------------------------------------------------------------
        */

        // 1. REGISTRASI SEMINAR / JURNAL
        Route::prefix('regis-semi')->name('regis-semi.')->group(function () {
            Route::get('/', [RegisSemiController::class, 'index'])->name('index');
            Route::post('/change', [RegisSemiController::class, 'postChange'])->name('change');
            Route::post('/delete', [RegisSemiController::class, 'postDelete'])->name('delete');
            Route::post('/delete-selected', [RegisSemiController::class, 'postDeleteSelected'])->name('delete-selected');
            
            // Detail & Undangan (Pastikan parameter {id} konsisten)
        Route::get('/{id}/detail', [RegisSemiController::class, 'show'])->name('detail');               Route::get('/{id}/result', [RegisSemiController::class, 'result'])->name('result');
            Route::get('/{id}/invite', [RegisSemiController::class, 'invite'])->name('invite');
            
            // Route khusus (perbaiki URL agar rapi)
            Route::get('/{id}/link-google-drive', [RegisSemiController::class, 'showInvite'])->name('link-google-drive');
        });

        Route::post('/submit/{id}', [PenghargaanBukuController::class, 'submit'])->name('submit');
        // 2. PENGHARGAAN & PUBLIKASI
        Route::prefix('penghargaan')->name('app.penghargaan.')->group(function () {
            
            // A. Buku
            Route::prefix('buku')->name('buku.')->group(function () {
                Route::get('/', [PenghargaanBukuController::class, 'index'])->name('index');
                Route::get('/ajukan', [PenghargaanBukuController::class, 'create'])->name('create');
                Route::post('/store', [PenghargaanBukuController::class, 'store'])->name('store');
                
                Route::get('/upload/{id}', [PenghargaanBukuController::class, 'uploadDocs'])->name('upload');
                Route::post('/upload/{id}', [PenghargaanBukuController::class, 'storeUpload'])->name('store-upload');
                
                // [BARIS INI YANG HILANG SEBELUMNYA]
                Route::post('/submit/{id}', [PenghargaanBukuController::class, 'submit'])->name('submit'); 

                Route::get('/detail/{id}', [PenghargaanBukuController::class, 'show'])->name('detail');
            });

            // B. PENGHARGAAN LAINNYA (Placeholder)
            Route::get('/dosen', function () { return 'Penghargaan Dosen UI (Coming Soon)'; })->name('dosen');
            Route::get('/mahasiswa', function () { return 'Penghargaan Mahasiswa UI (Coming Soon)'; })->name('mahasiswa');
            Route::get('/penelitian', function () { return 'Penghargaan Penelitian UI (Coming Soon)'; })->name('penelitian');
        });

        // NOTIFIKASI
        Route::get('/notifikasi', function () {
            return Inertia::render('app/notifikasi/page');
        })->name('notifications.index');

    }); // End Protected Routes

});