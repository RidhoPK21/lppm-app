<?php

namespace Tests\Feature\Livewire\Auth;

use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use App\Livewire\Auth\TotpLivewire;
use Livewire\Livewire;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TotpLivewireTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();
        // Clear session sebelum setiap test
        session()->flush();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        session()->flush();
        parent::tearDown();
    }

    #[Test]
    public function mount_redirect_ke_login_jika_token_kosong()
    {
        // Session kosong = ToolsHelper::getAuthToken() return ''
        Livewire::test(TotpLivewire::class)
            ->assertRedirectToRoute('auth.login');
    }

    #[Test]
    public function mount_set_qr_code_jika_api_success()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi postTotpSetup success dengan QR code
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) [
                    'qrCode' => 'qrcode-base64-data',
                ],
            ]);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);

        Livewire::test(TotpLivewire::class)
            ->assertSet('authToken', 'valid-token')
            ->assertSet('qrCode', 'qrcode-base64-data')
            ->assertStatus(200);
    }

    #[Test]
    public function mount_set_qr_code_null_jika_api_gagal()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi postTotpSetup gagal
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn((object) [
                'status' => 'error',
                'message' => 'Setup TOTP gagal',
            ]);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);

        Livewire::test(TotpLivewire::class)
            ->assertSet('authToken', 'valid-token')
            ->assertSet('qrCode', null)
            ->assertStatus(200);
    }

    #[Test]
    public function mount_set_qr_code_null_jika_api_return_null()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi postTotpSetup return null
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn(null);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);

        Livewire::test(TotpLivewire::class)
            ->assertSet('authToken', 'valid-token')
            ->assertSet('qrCode', null)
            ->assertStatus(200);
    }

    #[Test]
    public function submit_berhasil_dengan_token_valid()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi postTotpSetup dan postTotpVerify success
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) ['qrCode' => 'qrcode-data'],
            ]);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);
        $userApiMock
            ->shouldReceive('postTotpVerify')
            ->with('valid-token', '123456')
            ->andReturn((object) [
                'status' => 'success',
            ]);

        Livewire::test(TotpLivewire::class)
            ->set('token', '123456')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirectToRoute('app.beranda');
    }

    #[Test]
    public function submit_gagal_jika_token_kosong()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi postTotpSetup
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) ['qrCode' => 'qrcode-data'],
            ]);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);

        Livewire::test(TotpLivewire::class)
            ->set('token', '')
            ->call('submit')
            ->assertHasErrors(['token' => 'required'])
            ->assertSee('Kode TOTP harus diisi.')
            ->assertNoRedirect();
    }

    #[Test]
    public function submit_gagal_jika_token_bukan_angka()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi postTotpSetup
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) ['qrCode' => 'qrcode-data'],
            ]);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);

        Livewire::test(TotpLivewire::class)
            ->set('token', 'abc123')
            ->call('submit')
            ->assertHasErrors(['token' => 'numeric'])
            ->assertSee('Kode TOTP harus berupa angka.')
            ->assertNoRedirect();
    }

    #[Test]
    public function submit_gagal_jika_token_bukan_6_digit()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi postTotpSetup
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) ['qrCode' => 'qrcode-data'],
            ]);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);

        Livewire::test(TotpLivewire::class)
            ->set('token', '12345')  // 5 digit
            ->call('submit')
            ->assertHasErrors(['token' => 'digits'])
            ->assertSee('Kode TOTP harus terdiri dari 6 digit.')
            ->assertNoRedirect();
    }

    #[Test]
    public function submit_gagal_jika_auth_token_kosong_di_submit()
    {
        // Setup awal dengan token valid untuk bypass mount redirect
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi untuk mount
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) ['qrCode' => 'qrcode-data'],
            ]);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);

        $test = Livewire::test(TotpLivewire::class)
            ->assertSet('authToken', 'valid-token');

        // Set property authToken langsung ke empty string untuk simulasi kondisi kosong
        $test
            ->set('authToken', '')
            ->set('token', '123456')
            ->call('submit')
            ->assertHasErrors(['token'])
            ->assertSee('Token otentikasi tidak ditemukan.')
            ->assertNoRedirect();
    }

    #[Test]
    public function submit_gagal_jika_totp_verify_gagal()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) ['qrCode' => 'qrcode-data'],
            ]);
        $userApiMock
            ->shouldReceive('postTotpVerify')
            ->with('valid-token', '999999')
            ->andReturn((object) [
                'status' => 'error',
                'message' => 'Kode TOTP tidak valid',
            ]);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);

        Livewire::test(TotpLivewire::class)
            ->set('token', '999999')
            ->call('submit')
            ->assertHasErrors(['token'])
            ->assertSee('Kode TOTP tidak valid')
            ->assertNoRedirect();
    }

    #[Test]
    public function submit_gagal_jika_totp_verify_return_null()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) ['qrCode' => 'qrcode-data'],
            ]);
        $userApiMock
            ->shouldReceive('postTotpVerify')
            ->with('valid-token', '999999')
            ->andReturn(null);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);

        Livewire::test(TotpLivewire::class)
            ->set('token', '999999')
            ->call('submit')
            ->assertHasErrors(['token'])
            ->assertSee('Kode TOTP tidak valid, silakan coba lagi.')
            ->assertNoRedirect();
    }

    #[Test]
    public function submit_gagal_jika_totp_verify_tanpa_message()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) ['qrCode' => 'qrcode-data'],
            ]);
        $userApiMock
            ->shouldReceive('postTotpVerify')
            ->with('valid-token', '999999')
            ->andReturn((object) [
                'status' => 'error',
                // Tidak ada message
            ]);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);

        Livewire::test(TotpLivewire::class)
            ->set('token', '999999')
            ->call('submit')
            ->assertHasErrors(['token'])
            ->assertSee('Kode TOTP tidak valid, silakan coba lagi.')
            ->assertNoRedirect();
    }

    #[Test]
    public function submit_berhasil_dengan_token_6_digit_angka()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) ['qrCode' => 'qrcode-data'],
            ]);
        $userApiMock
            ->shouldReceive('postTotpVerify')
            ->with('valid-token', '999999')
            ->andReturn((object) [
                'status' => 'success',
            ]);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);

        Livewire::test(TotpLivewire::class)
            ->set('token', '999999')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirectToRoute('app.beranda');
    }

    #[Test]
    public function reset_error_bag_sebelum_validasi()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) ['qrCode' => 'qrcode-data'],
            ]);
        $userApiMock
            ->shouldReceive('postTotpVerify')
            ->with('valid-token', '123456')
            ->andReturn((object) [
                'status' => 'success',
            ]);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);

        $test = Livewire::test(TotpLivewire::class);

        // Buat error dengan submit data invalid terlebih dahulu
        $test
            ->set('token', 'abc')  // Data invalid
            ->call('submit')
            ->assertHasErrors(['token']);  // Pastikan ada error

        // Sekarang submit dengan data valid - error harus ter-reset
        $test
            ->set('token', '123456')  // Data valid
            ->call('submit')
            ->assertHasNoErrors()  // Error harus ter-reset
            ->assertRedirectToRoute('app.beranda');
    }

    #[Test]
    public function properti_terinisialisasi_dengan_null()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) ['qrCode' => 'qrcode-data'],
            ]);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);

        Livewire::test(TotpLivewire::class)
            ->assertSet('token', null);
    }

    #[Test]
    public function component_berhasil_dirender()
    {
        // Set token menggunakan ToolsHelper langsung
        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('postTotpSetup')
            ->with('valid-token')
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) ['qrCode' => 'qrcode-data'],
            ]);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => false,
                    ],
                ],
            ]);

        Livewire::test(TotpLivewire::class)
            ->assertStatus(200);
    }

    #[Test]
    public function tools_helper_berfungsi_dengan_baik()
    {
        // Test langsung functionality ToolsHelper
        ToolsHelper::setAuthToken('test-token-1');
        $this->assertEquals('test-token-1', ToolsHelper::getAuthToken());

        ToolsHelper::setAuthToken('');
        $this->assertEquals('', ToolsHelper::getAuthToken());

        ToolsHelper::setAuthToken('test-token-2');
        $this->assertEquals('test-token-2', ToolsHelper::getAuthToken());
    }

    #[Test]
    public function mount_dengan_auth_token_sudah_terverifikasi()
    {
        ToolsHelper::setAuthToken('verified-token');

        // Mock user api
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('getLoginInfo')
            ->with('verified-token')
            ->andReturn((object) [
                'data' => (object) [
                    'login_info' => (object) [
                        'is_verified' => true,
                    ],
                ],
            ]);

        // Pastikan redirect ke beranda
        $response = $this->get(route('auth.totp'));
        $response->assertRedirect(route('app.beranda'));
    }
}
