<?php

namespace App\Http\Middleware;

use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use App\Models\HakAksesModel;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $authToken = ToolsHelper::getAuthToken();

        if (empty($authToken)) {
            return redirect()->route('auth.login');
        }

        $response = UserApi::getMe($authToken);

        if (!isset($response->data->user)) {
            return redirect()->route('auth.login');
        }

        // Ambil user dari API
        $apiUser = $response->data->user;

        // Update atau buat user di Laravel
        $user = User::updateOrCreate(
           ['email' => $apiUser->email],
  
            [
                'name'      => $apiUser->name ?? null,
                'email'     => $apiUser->email ?? null,
                'username'  => $apiUser->username ?? null,
                'photo'     => $apiUser->photo ?? null,
                'password'  => bcrypt('dummy'), // Tidak dipakai
            ]
        );

        // Login Laravel
        if (!Auth::check() || Auth::id() !== $user->id) {
            Auth::login($user);
        }

        // Ambil hak akses lokal database
        $akses = HakAksesModel::where('user_id', $apiUser->id)->first();
        $apiUser->akses = $akses ? explode(',', $akses->akses) : [];

        // Simpan API user ke request
        $request->attributes->set('auth', $apiUser);

        return $next($request);
    }
}
