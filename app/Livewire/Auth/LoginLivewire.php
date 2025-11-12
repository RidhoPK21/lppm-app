<?php

namespace App\Livewire\Auth;

use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use Livewire\Component;

class LoginLivewire extends Component
{
    // Attributes
    public $systemId;

    public $info;

    public $username;

    public $password;

    // Fungsi yang dijalankan saat komponen di-mount
    public function mount()
    {
        $authToken = ToolsHelper::getAuthToken();
        if (! empty($authToken)) {
            $this->setup($authToken);
        }
    }

    // Fungsi yang dijalankan saat render
    public function render()
    {
        $urlLoginSSO = config('sdi.sso_authorize_url').'?'.http_build_query([
            'client_id' => config('sdi.sso_client_id'),
        ]);

        $data = [
            'urlLoginSSO' => $urlLoginSSO,
        ];

        return view('features.auth.login-livewire', $data);
    }

    // Fungsi untuk setup setelah mendapatkan auth token
    public function setup($authToken)
    {
        $resLoginInfo = UserApi::getLoginInfo($authToken);
        if ($resLoginInfo->status != 'success') {
            ToolsHelper::setAuthToken('');
            $this->dispatch('invalidAuthToken');

            return;
        }

        ToolsHelper::setAuthToken($authToken);

        $response = UserApi::getMe($authToken);
        if ($response->status != 'success') {
            return redirect()->route('auth.totp');
        }

        return redirect()->route('app.beranda');
    }

    // Fungsi yang menangani submit form login
    public function submit()
    {
        // Reset error bag sebelum validasi
        $this->resetErrorBag();

        // Validasi input
        $this->validate([
            'systemId' => 'required|string|max:50',
            'info' => 'required|string|max:255',
            'username' => 'required|string|max:50',
            'password' => 'required|string',
        ], [
            'systemId.required' => 'System ID harus diisi.',
            'systemId.string' => 'System ID harus berupa string.',
            'systemId.max' => 'System ID tidak boleh lebih dari 50 karakter.',
            'info.required' => 'Informasi harus diisi.',
            'info.string' => 'Informasi harus berupa string.',
            'info.max' => 'Informasi tidak boleh lebih dari 255 karakter.',
            'username.required' => 'Username harus diisi.',
            'username.string' => 'Username harus berupa string.',
            'username.max' => 'Username tidak boleh lebih dari 50 karakter.',
            'password.required' => 'Password harus diisi.',
            'password.string' => 'Password harus berupa string.',
        ]);

        $response = UserApi::postLogin(
            $this->systemId,
            config('app.name'),
            $this->info,
            $this->username,
            $this->password
        );

        if (! isset($response->data->token)) {
            $this->addError('username', $response->message ?? 'Gagal login, silakan coba lagi.');

            return;
        }

        ToolsHelper::setAuthToken($response->data->token);
        $this->dispatch('saveAuthToken', authToken: $response->data->token);
    }
}
