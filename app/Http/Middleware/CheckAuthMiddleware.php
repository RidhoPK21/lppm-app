<?php

namespace App\Http\Middleware;

use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use App\Models\HakAksesModel;
use App\Models\Profile;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $authToken = ToolsHelper::getAuthToken();

        if (empty($authToken)) {
            return redirect()->route('auth.login');
        }

        // Panggil API External untuk cek token
        $response = UserApi::getMe($authToken);

        // Jika respons API tidak valid
        if (! isset($response->data->user)) {
           Log::warning('API User data not found in response', [
    'response_keys' => array_keys((array) ($response->data ?? [])),
]);


            return redirect()->route('auth.login');
        }

        // Data User dari API External
        $apiUser = $response->data->user;

        // ✅ FIXED: Properti sudah dipastikan ada (non-nullable) oleh PHPStan
        $apiId = (string) $apiUser->id;
        $apiEmail = (string) $apiUser->email;
        $apiName = (string) $apiUser->name;

        Log::info('API User Data Received', [
            'api_user_id' => $apiId,
            'email' => $apiEmail,
        ]);

        // === SINKRONISASI USER (API -> LOCAL DB) ===

        // 1. Cari User di DB Lokal (Prioritas: ID, lalu Email)
        /** @var User|null $laravelUser */
        $laravelUser = User::where('id', $apiId)->first();

        if (! $laravelUser && ! empty($apiEmail)) {
            $laravelUser = User::where('email', $apiEmail)->first();
        }

        // 2. Jika User belum ada di DB Lokal, Buat Baru
        if (! $laravelUser) {
            try {
                // Generate default jika kosong
                $emailToUse = ! empty($apiEmail) 
                    ? $apiEmail 
                    : ($apiId.'@'.config('app.domain', 'example.com'));

                $nameToUse = ! empty($apiName) 
                    ? $apiName 
                    : ('User_'.substr($apiId, 0, 8));

                $laravelUser = User::create([
                    'id' => $apiId, // Paksa pakai ID dari API (UUID)
                    'name' => $nameToUse,
                    'email' => $emailToUse,
                    'username' => $apiUser->username ?? Str::slug($nameToUse),
                    'password' => bcrypt(Str::random(32)),
                ]);

                Log::info('Created NEW Laravel user from API data', ['user_id' => $laravelUser->id]);

            } catch (\Exception $e) {
                Log::error('Failed to create Laravel user', ['error' => $e->getMessage()]);
                // Jika gagal create, abort atau redirect error
                return redirect()->route('auth.login')->with('error', 'Gagal sinkronisasi data user.');
            }
        }

        // 3. Pastikan Profile Ada (Opsional, tapi bagus untuk konsistensi)
        try {
            Profile::firstOrCreate(
                ['user_id' => $laravelUser->id],
                ['name' => $laravelUser->name]
            );
        } catch (\Exception $e) {
            // Ignore profile creation error non-critical
        }

        // 4. Login User ke Laravel Session
        if (! Auth::check() || Auth::id() !== $laravelUser->id) {
            Auth::login($laravelUser);
        }

        // 5. Ambil Hak Akses User
        /** @var HakAksesModel|null $aksesRecord */
        $aksesRecord = HakAksesModel::where('user_id', $laravelUser->id)->first();
        
        // Cek null sebelum akses properti 'akses'
        $userAkses = ($aksesRecord && isset($aksesRecord->akses)) 
            ? array_map('trim', explode(',', $aksesRecord->akses)) 
            : [];

        // 6. Set data ke Request Attributes agar bisa diakses di Controller
        $apiUser->laravel_user_id = $laravelUser->id;
        $apiUser->akses = $userAkses;
        $apiUser->roles = $userAkses; // Alias untuk roles

        $request->attributes->set('auth', $apiUser);
        $request->attributes->set('laravel_user', $laravelUser);

        // Debug log
        Log::info('User Authenticated via Middleware', [
            'user_id' => $laravelUser->id,
            'roles' => $userAkses,
        ]);

        return $next($request);
    }
}