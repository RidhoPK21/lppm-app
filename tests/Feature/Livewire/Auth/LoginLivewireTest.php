<?php

namespace Tests\Feature\Livewire\Auth;

use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use App\Livewire\Auth\LoginLivewire;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Mockery;

class LoginLivewireTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();

        // Clear session sebelum setiap test
        session()->flush();

        // Set config untuk testing
        config([
            'sdi.sso_authorize_url' => 'https://sso.example.com/authorize',
            'sdi.sso_client_id' => 'test-client-id',
            'app.name' => 'Aplikasi Testing',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        session()->flush();
        parent::tearDown();
    }

    #[Test]
    public function component_berhasil_dirender_dengan_url_sso()
    {
        // ToolsHelper::getAuthToken() akan return '' karena session kosong
        Livewire::test(LoginLivewire::class)
            ->assertSee('Masuk dengan SSO');
    }

    #[Test]
    public function mount_tidak_memanggil_setup_jika_token_kosong()
    {
        // Session kosong = ToolsHelper::getAuthToken() return ''

        // UserApi tidak boleh dipanggil
        $userApiMock = Mockery::mock('alias:' . UserApi::class);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->never();
        $userApiMock
            ->shouldReceive('getMe')
            ->never();

        Livewire::test(LoginLivewire::class)
            ->assertStatus(200);
    }

    #[Test]
    public function mount_setup_redirect_ke_beranda_jika_semua_kondisi_terpenuhi()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi semua success
        $userApiMock = Mockery::mock('alias:' . UserApi::class);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) ['status' => 'success']);
        $userApiMock
            ->shouldReceive('getMe')
            ->with('valid-token')
            ->andReturn((object) ['status' => 'success']);

        Livewire::test(LoginLivewire::class)
            ->assertRedirectToRoute('app.beranda');

        // Verify token tetap tersimpan
        $this->assertEquals('valid-token', ToolsHelper::getAuthToken());
    }

    #[Test]
    public function mount_setup_redirect_ke_totp_jika_get_me_bukan_success()
    {
        // Set token menggunakan ToolsHelper
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi: getLoginInfo success tapi getMe bukan success
        $userApiMock = Mockery::mock('alias:' . UserApi::class);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) ['status' => 'success']);
        $userApiMock
            ->shouldReceive('getMe')
            ->with('valid-token')
            ->andReturn((object) ['status' => 'error']);

        Livewire::test(LoginLivewire::class)
            ->assertRedirectToRoute('auth.totp');

        // Token harus tetap tersimpan
        $this->assertEquals('valid-token', ToolsHelper::getAuthToken());
    }

    #[Test]
    public function mount_setup_dispatch_event_jika_get_login_info_gagal()
    {
        // Set token menggunakan ToolsHelper
        ToolsHelper::setAuthToken('invalid-token');

        // Mock UserApi: getLoginInfo gagal
        $userApiMock = Mockery::mock('alias:' . UserApi::class);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('invalid-token')
            ->andReturn((object) ['status' => 'error']);

        Livewire::test(LoginLivewire::class)
            ->assertDispatched('invalidAuthToken');

        // Token harus di-clear
        $this->assertEquals('', ToolsHelper::getAuthToken());
    }

    #[Test]
    public function submit_berhasil_dengan_data_valid_dan_mendapat_token()
    {
        // Mock UserApi postLogin success dengan token
        $userApiMock = Mockery::mock('alias:' . UserApi::class);
        $userApiMock
            ->shouldReceive('postLogin')
            ->with(
                'system-123',
                'Aplikasi Testing',
                'Test information',
                'testuser',
                'password123'
            )
            ->andReturn((object) [
                'data' => (object) ['token' => 'login-token-123'],
            ]);

        Livewire::test(LoginLivewire::class)
            ->set('systemId', 'system-123')
            ->set('info', 'Test information')
            ->set('username', 'testuser')
            ->set('password', 'password123')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertDispatched('saveAuthToken', authToken: 'login-token-123');

        // Token harus tersimpan di session
        $this->assertEquals('login-token-123', ToolsHelper::getAuthToken());
    }

    #[Test]
    public function submit_gagal_jika_api_tidak_mengembalikan_token()
    {
        // Mock UserApi postLogin tanpa token
        $userApiMock = Mockery::mock('alias:' . UserApi::class);
        $userApiMock
            ->shouldReceive('postLogin')
            ->andReturn((object) [
                'message' => 'Invalid credentials',
            ]);

        Livewire::test(LoginLivewire::class)
            ->set('systemId', 'system-123')
            ->set('info', 'Test info')
            ->set('username', 'testuser')
            ->set('password', 'password123')
            ->call('submit')
            ->assertHasErrors(['username'])
            ->assertSee('Invalid credentials');

        // Token tidak boleh tersimpan
        $this->assertEquals('', ToolsHelper::getAuthToken());
    }

    #[Test]
    public function submit_gagal_jika_api_mengembalikan_response_tanpa_data_property()
    {
        // Mock UserApi postLogin tanpa data property
        $userApiMock = Mockery::mock('alias:' . UserApi::class);
        $userApiMock
            ->shouldReceive('postLogin')
            ->andReturn((object) [
                'status' => 'error',
                'message' => 'Server error',
            ]);

        Livewire::test(LoginLivewire::class)
            ->set('systemId', 'system-123')
            ->set('info', 'Test info')
            ->set('username', 'testuser')
            ->set('password', 'password123')
            ->call('submit')
            ->assertHasErrors(['username'])
            ->assertSee('Server error');
    }

    #[Test]
    public function submit_gagal_jika_api_mengembalikan_null()
    {
        // Mock UserApi postLogin return null
        $userApiMock = Mockery::mock('alias:' . UserApi::class);
        $userApiMock
            ->shouldReceive('postLogin')
            ->andReturn(null);

        Livewire::test(LoginLivewire::class)
            ->set('systemId', 'system-123')
            ->set('info', 'Test info')
            ->set('username', 'testuser')
            ->set('password', 'password123')
            ->call('submit')
            ->assertHasErrors(['username'])
            ->assertSee('Gagal login, silakan coba lagi.');
    }

    #[Test]
    public function submit_menggunakan_app_name_dari_config()
    {
        // Mock UserApi dan verify platform dari config
        $userApiMock = Mockery::mock('alias:' . UserApi::class);
        $userApiMock
            ->shouldReceive('postLogin')
            ->with(
                'system-123',
                'Aplikasi Testing',  // Harus dari config('app.name')
                'Test info',
                'testuser',
                'password123'
            )
            ->andReturn((object) [
                'data' => (object) ['token' => 'token-123'],
            ]);

        Livewire::test(LoginLivewire::class)
            ->set('systemId', 'system-123')
            ->set('info', 'Test info')
            ->set('username', 'testuser')
            ->set('password', 'password123')
            ->call('submit')
            ->assertHasNoErrors();
    }

    #[Test]
    public function validasi_gagal_untuk_semua_field_kosong()
    {
        Livewire::test(LoginLivewire::class)
            ->set('systemId', '')
            ->set('info', '')
            ->set('username', '')
            ->set('password', '')
            ->call('submit')
            ->assertHasErrors([
                'systemId' => 'required',
                'info' => 'required',
                'username' => 'required',
                'password' => 'required',
            ])
            ->assertSee('Username harus diisi.')
            ->assertSee('Password harus diisi.');
    }

    #[Test]
    public function validasi_berhasil_untuk_tepat_batas_maksimal()
    {
        // Mock UserApi
        $userApiMock = Mockery::mock('alias:' . UserApi::class);
        $userApiMock
            ->shouldReceive('postLogin')
            ->andReturn((object) [
                'data' => (object) ['token' => 'token-123'],
            ]);

        Livewire::test(LoginLivewire::class)
            ->set('systemId', str_repeat('a', 50))
            ->set('info', str_repeat('b', 255))
            ->set('username', str_repeat('c', 50))
            ->set('password', 'password123')
            ->call('submit')
            ->assertHasNoErrors();
    }

    #[Test]
    public function properti_terinisialisasi_dengan_null()
    {
        Livewire::test(LoginLivewire::class)
            ->assertSet('systemId', null)
            ->assertSet('info', null)
            ->assertSet('username', null)
            ->assertSet('password', null);
    }

    #[Test]
    public function tools_helper_set_dan_get_auth_token_berfungsi()
    {
        // Test ToolsHelper functionality langsung
        ToolsHelper::setAuthToken('test-token');
        $this->assertEquals('test-token', ToolsHelper::getAuthToken());

        ToolsHelper::setAuthToken('');
        $this->assertEquals('', ToolsHelper::getAuthToken());

        ToolsHelper::setAuthToken('another-token');
        $this->assertEquals('another-token', ToolsHelper::getAuthToken());
    }
}
