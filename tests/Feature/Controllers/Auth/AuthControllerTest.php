<?php

namespace Tests\Feature\Controllers\Auth;

use App\Helper\ApiHelper;
use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use App\Http\Controllers\Auth\AuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Inertia::always agar tidak error saat controller memanggilnya
        Inertia::shouldReceive('always')
            ->andReturnUsing(function ($value) {
                return Mockery::mock('overload:Inertia\AlwaysProp', [
                    'getValue' => $value,
                ]);
            });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function login_menampilkan_halaman_login_dengan_url_sso()
    {
        config(['sdi.sso_authorize_url' => 'https://sso.example.com/auth']);
        config(['sdi.sso_client_id' => 'test-client-id']);

        $expectedUrl = 'https://sso.example.com/auth?client_id=test-client-id';
        $mockResponse = Mockery::mock(Response::class);

        Inertia::shouldReceive('render')
            ->once()
            ->with('auth/login-page', ['urlLoginSSO' => $expectedUrl])
            ->andReturn($mockResponse);

        $controller = new AuthController;
        $response = $controller->login();

        $this->assertSame($mockResponse, $response);
    }

    #[Test]
    public function post_login_check_berhasil_dan_redirect_ke_home()
    {
        $authToken = 'valid-token-123';

        // Mock UserApi
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getLoginInfo')
            ->with($authToken)
            ->andReturn((object) ['status' => 'success']);

        // Mock getMe dengan struktur lengkap untuk loginLaravelUser
        $userApiMock->shouldReceive('getMe')
            ->with($authToken)
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) [
                    'user' => (object) [
                        'id' => 'uuid-123',
                        'name' => 'Test User',
                        'email' => 'test@del.ac.id',
                    ],
                ],
            ]);

        $request = new Request(['authToken' => $authToken]);
        $controller = new AuthController;

        $response = $controller->postLoginCheck($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('home'), $response->getTargetUrl());

        // Pastikan user tersimpan di DB
        $this->assertDatabaseHas('users', ['email' => 'test@del.ac.id']);
    }

    #[Test]
    public function post_login_check_gagal_dan_redirect_ke_logout()
    {
        $authToken = 'invalid-token';

        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getLoginInfo')
            ->with($authToken)
            ->andReturn((object) ['status' => 'error']);

        $request = new Request(['authToken' => $authToken]);
        $controller = new AuthController;

        $response = $controller->postLoginCheck($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('auth.logout'), $response->getTargetUrl());
    }

    #[Test]
    public function post_login_berhasil_dan_redirect_ke_totp()
    {
        $userApiMock = Mockery::mock('alias:'.UserApi::class);

        // Mock postLogin success
        $userApiMock->shouldReceive('postLogin')
            ->andReturn((object) [
                'data' => (object) ['token' => 'login-token-123'],
            ]);

        // Mock getMe (dipanggil setelah token didapat)
        $userApiMock->shouldReceive('getMe')
            ->with('login-token-123')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) [
                    'user' => (object) [
                        'id' => 'uuid-login-123',
                        'name' => 'Login User',
                        'email' => 'login@test.com',
                    ],
                ],
            ]);

        $request = new Request([
            'username' => 'testuser',
            'password' => 'password123',
            'systemId' => 'TestSystem',
            'info' => 'TestInfo',
        ]);

        $controller = new AuthController;
        $response = $controller->postLogin($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('auth.totp'), $response->getTargetUrl());

        // Verifikasi token tersimpan di session/helper
        $this->assertEquals('login-token-123', ToolsHelper::getAuthToken());
    }

    #[Test]
    public function post_login_redirect_back_dengan_error_jika_token_tidak_ada()
    {
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('postLogin')
            ->andReturn((object) [
                'data' => (object) [], // Token tidak ada
            ]);

        $request = new Request([
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $controller = new AuthController;
        $response = $controller->postLogin($request);

        $this->assertEquals(302, $response->getStatusCode());
        $sessionErrors = $response->getSession()->get('errors');
        $this->assertNotNull($sessionErrors);
    }

    #[Test]
    public function post_login_check_redirect_ke_totp_jika_get_me_gagal()
    {
        $authToken = 'valid-token-123';

        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getLoginInfo')
            ->with($authToken)
            ->andReturn((object) ['status' => 'success']);

        $userApiMock->shouldReceive('getMe')
            ->with($authToken)
            ->andReturn((object) ['status' => 'error']);

        $request = new Request(['authToken' => $authToken]);
        $controller = new AuthController;

        $response = $controller->postLoginCheck($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('auth.totp'), $response->getTargetUrl());
    }

    #[Test]
    public function logout_menghapus_token_dan_menampilkan_halaman_logout()
    {
        ToolsHelper::setAuthToken('previous-token');

        $mockResponse = Mockery::mock(Response::class);
        Inertia::shouldReceive('render')
            ->once()
            ->with('auth/logout-page')
            ->andReturn($mockResponse);

        $controller = new AuthController;
        $response = $controller->logout();

        $this->assertSame($mockResponse, $response);
        $this->assertEquals('', ToolsHelper::getAuthToken());
    }

    #[Test]
    public function totp_redirect_ke_login_jika_token_tidak_ada()
    {
        ToolsHelper::setAuthToken('');

        $controller = new AuthController;
        $response = $controller->totp();

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('auth.login'), $response->getTargetUrl());
    }

    #[Test]
    public function post_totp_berhasil_dan_redirect_ke_home()
    {
        $authToken = 'totp-token-123';
        ToolsHelper::setAuthToken($authToken);

        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('postTotpVerify')
            ->with($authToken, '123456')
            ->andReturn((object) ['status' => 'success']);

        $request = new Request(['kodeOTP' => '123456']);
        $controller = new AuthController;

        $response = $controller->postTotp($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('home'), $response->getTargetUrl());
    }

    #[Test]
    public function totp_redirect_ke_logout_jika_get_login_info_gagal()
    {
        $authToken = 'invalid-token';
        ToolsHelper::setAuthToken($authToken);

        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getLoginInfo')
            ->with($authToken)
            ->andReturn((object) ['status' => 'error']);

        $controller = new AuthController;
        $response = $controller->totp();

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('auth.logout'), $response->getTargetUrl());
    }

    #[Test]
    public function totp_redirect_ke_home_jika_get_me_sukses()
    {
        $authToken = 'valid-token';
        ToolsHelper::setAuthToken($authToken);

        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        // Login info OK
        $userApiMock->shouldReceive('getLoginInfo')
            ->with($authToken)
            ->andReturn((object) ['status' => 'success']);

        // Get Me OK -> means already authenticated, go home
        $userApiMock->shouldReceive('getMe')
            ->with($authToken)
            ->andReturn((object) ['status' => 'success']);

        $controller = new AuthController;
        $response = $controller->totp();

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('home'), $response->getTargetUrl());
    }

    #[Test]
    public function totp_menampilkan_halaman_dengan_qr_code_jika_get_me_gagal()
    {
        $authToken = 'valid-token-totp';
        ToolsHelper::setAuthToken($authToken);

        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getLoginInfo')
            ->andReturn((object) ['status' => 'success']);

        // Get Me Gagal/Pending -> Show TOTP Page
        $userApiMock->shouldReceive('getMe')
            ->andReturn((object) ['status' => 'error']);

        $userApiMock->shouldReceive('postTotpSetup')
            ->with($authToken)
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) ['qrCode' => 'qrcode-data'],
            ]);

        $mockResponse = Mockery::mock(Response::class);
        Inertia::shouldReceive('render')
            ->with('auth/totp-page', [
                'authToken' => $authToken,
                'qrCode' => 'qrcode-data',
            ])
            ->andReturn($mockResponse);

        $controller = new AuthController;
        $response = $controller->totp();

        $this->assertSame($mockResponse, $response);
    }

    #[Test]
    public function sso_callback_berhasil_dan_redirect_ke_home()
    {
        config(['sdi.sso_token_url' => 'https://sso.example.com/token']);
        config(['sdi.sso_client_id' => 'test-client']);
        config(['sdi.sso_client_secret' => 'test-secret']);

        // Mock ApiHelper karena dipanggil langsung oleh AuthController::ssoCallback
        $apiHelperMock = Mockery::mock('alias:'.ApiHelper::class);
        $apiHelperMock->shouldReceive('sendRequest')
            ->andReturn((object) ['access_token' => 'sso-token-123']);

        // Mock UserApi karena juga dipanggil untuk getMe
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getMe')
            ->with('sso-token-123')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) [
                    'user' => (object) [
                        'id' => 'uuid-sso-123',
                        'name' => 'User SSO',
                        'email' => 'sso@test.com',
                    ],
                ],
            ]);

        $request = new Request(['code' => 'auth-code-123']);
        $controller = new AuthController;
        $response = $controller->ssoCallback($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('home'), $response->getTargetUrl());
        $this->assertEquals('sso-token-123', ToolsHelper::getAuthToken());
    }

    #[Test]
    public function sso_callback_redirect_ke_login_jika_code_tidak_ada()
    {
        $request = new Request;
        $controller = new AuthController;
        $response = $controller->ssoCallback($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('auth.login'), $response->getTargetUrl());
    }
}
