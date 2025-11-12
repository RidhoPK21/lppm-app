<?php

namespace App\Livewire\Auth;

use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use Livewire\Component;

class TotpLivewire extends Component
{
    // Attributes
    public $authToken = '';

    public $qrCode;

    public $token;

    // Fungsi yang dijalankan saat komponen di-mount
    public function mount()
    {
        $this->authToken = ToolsHelper::getAuthToken();
        if (empty($this->authToken)) {
            return redirect()->route('auth.login');
        }

        $checkToken = UserApi::getLoginInfo($this->authToken);
        // dd($checkToken);
        if (isset($checkToken->data->login_info->is_verified) && $checkToken->data->login_info->is_verified) {
            return redirect()->route('app.beranda');
        }
        // dd($checkToken);

        $response = UserApi::postTotpSetup($this->authToken);

        if (!$response || $response->status != 'success') {
            $this->qrCode = null;
        } else {
            $this->qrCode = $response->data->qrCode;
        }
    }

    // Fungsi yang dijalankan saat render
    public function render()
    {
        return view('features.auth.totp-livewire');
    }

    // Fungsi yang menangani submit form TOTP
    public function submit()
    {
        // Reset error bag sebelum validasi
        $this->resetErrorBag();

        // Validasi input
        $this->validate([
            'token' => 'required|numeric|digits:6',
        ], [
            'token.required' => 'Kode TOTP harus diisi.',
            'token.numeric' => 'Kode TOTP harus berupa angka.',
            'token.digits' => 'Kode TOTP harus terdiri dari 6 digit.',
        ]);

        if (empty($this->authToken)) {
            $this->addError('token', 'Token otentikasi tidak ditemukan.');

            return;
        }

        $response = UserApi::postTotpVerify($this->authToken, $this->token);
        if (!$response || $response->status != 'success') {
            $this->addError('token', $response->message ?? 'Kode TOTP tidak valid, silakan coba lagi.');

            return;
        }

        return redirect()->route('app.beranda');
    }
}
