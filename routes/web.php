
<?php

use App\Http\Controllers\App\HakAkses\HakAksesController;
use App\Http\Controllers\App\Home\HomeController;
use App\Http\Controllers\App\Todo\TodoController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
use Inertia\Inertia;

Route::middleware(['throttle:req-limit', 'handle.inertia'])->group(function () {
    // SSO Routes
    Route::group(['prefix' => 'sso'], function () {
        Route::get('/callback', [AuthController::class, 'ssoCallback'])->name('sso.callback');
    });

    // Authentication Routes
    Route::prefix('auth')->group(function () {
        // Login Routes
        Route::get('/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('/login-check', [AuthController::class, 'postLoginCheck'])->name('auth.login-check');
        Route::post('/login-post', [AuthController::class, 'postLogin'])->name('auth.login-post');

        // Logout Route
        Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

        // TOTP Routes
        Route::get('/totp', [AuthController::class, 'totp'])->name('auth.totp');
        Route::post('/totp-post', [AuthController::class, 'postTotp'])->name('auth.totp-post');
    });

    // Protected Routes
    Route::group(['middleware' => 'check.auth'], function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');

        // Hak Akses Routes
        Route::prefix('hak-akses')->group(function () {
            Route::get('/', [HakAksesController::class, 'index'])->name('hak-akses');
            Route::post('/change', [HakAksesController::class, 'postChange'])->name('hak-akses.change-post');
            Route::post('/delete', [HakAksesController::class, 'postDelete'])->name('hak-akses.delete-post');
            Route::post('/delete-selected', [HakAksesController::class, 'postDeleteSelected'])->name('hak-akses.delete-selected-post');
        });

        // Todo Routes
        Route::prefix('todo')->group(function () {
            Route::get('/', [TodoController::class, 'index'])->name('todo');
            Route::post('/change', [TodoController::class, 'postChange'])->name('todo.change-post');
            Route::post('/delete', [TodoController::class, 'postDelete'])->name('todo.delete-post');
        });
    });

    // LPPM Specific Routes Trying

    // LPPM - UI Testing
    Route::get('/lppm/todo-ui', function () {
        return inertia('app/lppm/todo-ui', [
            'pageName' => 'Todo UI Test'
        ]);
    })->name('lppm.todo-ui');

    // Nanti digunakan sebagai submenu
    Route::get('/registrasi/seminar', function () {
        return 'Registrasi Seminar UI (dummy)';
    })->name('registrasi.seminar');

    Route::get('/registrasi/jurnal', function () {
        return 'Registrasi Jurnal UI (dummy)';
    })->name('registrasi.jurnal');




    
    // LPPM - Penghargaan (submenu)
    Route::prefix('penghargaan')->group(function () {

        Route::get('/dosen', function () {
            return 'Penghargaan Dosen UI (dummy)';
        })->name('penghargaan.dosen');

        Route::get('/mahasiswa', function () {
            return 'Penghargaan Mahasiswa UI (dummy)';
        })->name('penghargaan.mahasiswa');

        Route::get('/penelitian', function () {
            return 'Penghargaan Penelitian UI (dummy)';
        })->name('penghargaan.penelitian');
    });
});