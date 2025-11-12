<?php

namespace Tests\Feature\Controllers;

use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use App\Livewire\Auth\LoginLivewire;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Mockery;

class AuthControllerTest extends TestCase
{
    // Setup sebelum setiap test => dipanggil sebelum test dijalankan
    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();

        $this->setupDefaultMocks();
    }

    // Tear down setelah setiap test => dipanggil setelah test selesai
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // Setup mock default yang digunakan di banyak test
    private function setupDefaultMocks()
    {
        $fakeToken = 'fake-token';
        ToolsHelper::setAuthToken($fakeToken);

        // Mock LoginLivewire component
        Livewire::component('auth.login-livewire', LoginLivewire::class);
        $mockLoginLivewire = Mockery::mock(LoginLivewire::class)->makePartial();
        $mockLoginLivewire
            ->shouldReceive('setup')
            ->with($fakeToken)
            ->andReturnNull();
        $mockLoginLivewire
            ->shouldReceive('render')
            ->andReturn(view('features.auth.login-livewire'));

        // Bind mock instance ke container
        $this->app->instance(LoginLivewire::class, $mockLoginLivewire);
    }

    #[Test]
    public function login_berhasil_mengembalikan_view()
    {
        $fakeToken = 'fake-token';

        // Mock static class UserApi
        $userApiMock = Mockery::mock('alias:' . UserApi::class);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with($fakeToken)
            ->andReturn((object) [
                'status' => 'fail',
            ]);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with($fakeToken)
            ->andReturnNull();

        // Act - Panggil route
        $response = $this->get('/auth/login');

        // Assert - Verifikasi hasil
        $response->assertOk();
        $response->assertViewIs('features.auth.login-page');
    }

    #[Test]
    public function totp_berhasil_mengembalikan_view()
    {
        $fakeToken = 'fake-token';

        // Mock static class UserApi
        $userApiMock = Mockery::mock('alias:' . UserApi::class);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with($fakeToken)
            ->andReturn((object) [
                'status' => 'fail',
            ]);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with($fakeToken)
            ->andReturnNull();

        // Act - Panggil route
        $response = $this->get('/auth/totp');

        // Assert - Verifikasi hasil
        $response->assertOk();
        $response->assertViewIs('features.auth.totp-page');
    }

    #[Test]
    public function logout_berhasil_mengembalikan_view()
    {
        // Act - Panggil route
        $response = $this->get('/auth/logout');

        // Assert - Verifikasi hasil
        $response->assertOk();
        $response->assertViewIs('features.auth.logout-page');
    }

    #[Test]
    public function sso_callback_tanpa_code_mengarahkan_ke_login_dengan_error()
    {
        // Act - Panggil route tanpa parameter 'code'
        $response = $this->get('/sso/callback');

        // Assert - Verifikasi hasil
        $response->assertRedirect(route('auth.login'));
        $response->assertSessionHas('error', 'Kode otorisasi tidak ditemukan');
    }

    #[Test]
    public function sso_callback_dengan_code_gagal_mendapatkan_token_mengarahkan_ke_login_dengan_error()
    {
        // Act - Panggil route dengan parameter 'code' yang valid tapi gagal mendapatkan token
        $response = $this->get('/sso/callback?code=valid-code');

        // Assert - Verifikasi hasil
        $response->assertRedirect(route('auth.login'));
        $response->assertSessionHas('error', 'Gagal mendapatkan token akses dari SSO');
    }

    #[Test]
    public function sso_callback_dengan_code_berhasil_mengarahkan_ke_beranda()
    {
        // Gunakan Http fake instead of mocking ApiHelper
        Http::fake([
            config('sdi.sso_token_url') => Http::response([
                'access_token' => 'valid-access-token',
            ], 200),
        ]);

        // Act - Panggil route dengan parameter 'code' yang valid
        $response = $this->get('/sso/callback?code=valid-code');

        // Assert - Verifikasi hasil
        $response->assertRedirect(route('app.beranda'));
    }
}
