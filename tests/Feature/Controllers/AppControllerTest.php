<?php

namespace Tests\Feature\Controllers;

use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use App\Models\HakAksesModel;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Mockery;

class AppControllerTest extends TestCase
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

    // Data token palsu untuk mock

    private $fakeToken = 'fake-token';

    // Data user palsu untuk mock
    private $userData = [
        'id' => '8357fda6-67f7-4a99-8f01-9847d6920596',
        'username' => 'abdullah',
        'name' => 'Abdullah',
        'alias' => 'Staff',
        'gender' => 'M',
        'roles' => ['Admin'],
    ];

    // Setup mock default yang digunakan di banyak test
    private function setupDefaultMocks()
    {
        ToolsHelper::setAuthToken($this->fakeToken);

        // Mock static class UserApi
        $userApiMock = Mockery::mock('alias:' . UserApi::class);
        $userApiMock
            ->shouldReceive('getMe')
            ->with($this->fakeToken)
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) [
                    'user' => (object) $this->userData,
                ],
            ]);
        $userApiMock
            ->shouldReceive('postReqUsersByIds')
            ->with($this->fakeToken, Mockery::any())
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) [
                    'users' => [(object) $this->userData],
                ],
            ]);

        // Mock HakAksesModel
        $hakAksesMock = Mockery::mock('alias:' . HakAksesModel::class);
        $hakAksesMock
            ->shouldReceive('get')
            ->andReturn(
                collect([])
            );
        $hakAksesMock
            ->shouldReceive('where')
            ->with('user_id', $this->userData['id'])
            ->once()
            ->andReturnSelf();
        $hakAksesMock
            ->shouldReceive('first')
            ->once()
            ->andReturn((object) ['roles' => $this->userData['roles']]);
    }

    #[Test]
    public function beranda_berhasil_mengembalikan_view()
    {
        // Act - Panggil route
        $response = $this->get('/app/beranda');

        // Assert - Verifikasi hasil
        $response->assertOk();
        $response->assertViewIs('features.beranda.beranda-page');
        $response->assertViewHas('auth');

        // Verifikasi data auth
        $viewAuth = $response->viewData('auth');
        $this->assertEquals($this->userData['id'], $viewAuth->id);
        $this->assertEquals($this->userData['name'], $viewAuth->name);
        $this->assertEquals($this->userData['username'], $viewAuth->username);
        $this->assertEquals($this->userData['roles'], $viewAuth->roles);
    }

    #[Test]
    public function hak_akses_berhasil_mengembalikan_view()
    {
        // Mock fungsi UserApi::postReqUsersByIds()
        Mockery::mock('alias:' . UserApi::class)
            ->shouldReceive('postReqUsersByIds')
            ->with($this->fakeToken, Mockery::any())
            ->andReturn([
                'status' => 'success',
                'data' => [
                    'users' => [
                        (object) $this->userData,
                    ],
                ],
            ]);

        // Act - Panggil route
        $response = $this->get('/app/hak-akses');

        // Assert - Verifikasi hasil
        $response->assertOk();
        $response->assertViewIs('features.hak-akses.hak-akses-page');
        $response->assertViewHas('auth');

        // Verifikasi data auth
        $viewAuth = $response->viewData('auth');
        $this->assertEquals($this->userData['id'], $viewAuth->id);
        $this->assertEquals($this->userData['name'], $viewAuth->name);
        $this->assertEquals($this->userData['username'], $viewAuth->username);
        $this->assertEquals($this->userData['roles'], $viewAuth->roles);
    }
}
