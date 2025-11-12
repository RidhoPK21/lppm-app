<?php

namespace App\Http\Controllers;

use App\Helper\ApiHelper;
use App\Helper\ToolsHelper;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login()
    {
        return view('features.auth.login-page');
    }

    public function totp()
    {
        return view('features.auth.totp-page');
    }

    public function logout()
    {
        ToolsHelper::setAuthToken('');

        return view('features.auth.logout-page');
    }

    // SSO Callback
    public function ssoCallback(Request $request)
    {
        $code = $request->query('code');
        if (!$code) {
            return redirect()->route('auth.login')->with('error', 'Kode otorisasi tidak ditemukan');
        }

        $urlToken = config('sdi.sso_token_url');

        $response = ApiHelper::sendRequest($urlToken, 'POST', [
            'client_id' => config('sdi.sso_client_id'),
            'client_secret' => config('sdi.sso_client_secret'),
            'code' => $code,
        ]);

        if (!isset($response->access_token)) {
            return redirect()->route('auth.login')->with('error', 'Gagal mendapatkan token akses dari SSO');
        }

        // Simpan token akses di sesi atau cookie
        ToolsHelper::setAuthToken($response->access_token);

        return redirect()->route('app.beranda');
    }
}
