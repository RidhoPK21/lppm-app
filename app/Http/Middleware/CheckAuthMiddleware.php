<?php

namespace App\Http\Middleware;

use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use App\Models\HakAksesModel;
use App\Models\User;
use App\Models\Profile;
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

        $apiUser = $response->data->user;

        // Update atau buat user di Laravel
        $user = User::updateOrCreate(
            ['email' => $apiUser->email],
            [
                'name' => $apiUser->name ?? null,
                'email' => $apiUser->email ?? null,
                'username' => $apiUser->username ?? null,
                'photo' => $apiUser->photo ?? null,
                'password' => bcrypt('dummy'),
            ]
        );

        // Pastikan profile ada untuk user ini
        Profile::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => $user->name]
        );

        // Login Laravel
        if (!Auth::check() || Auth::id() !== $user->id) {
            Auth::login($user);
        }

        // Ambil hak akses
        $akses = HakAksesModel::where('user_id', $apiUser->id)->first();
        $apiUser->akses = $akses ? explode(',', $akses->akses) : [];

        $request->attributes->set('auth', $apiUser);

        return $next($request);
    }
}