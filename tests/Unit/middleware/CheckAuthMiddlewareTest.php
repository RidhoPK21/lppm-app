<?php

namespace Tests\Unit\Middleware;

use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use App\Http\Middleware\CheckAuthMiddleware;
use App\Models\HakAksesModel;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckAuthMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function redirect_ke_login_jika_token_tidak_ada()
    {
        ToolsHelper::setAuthToken('');

        $request = Request::create('/app/profile', 'GET');
        $middleware = new CheckAuthMiddleware;

        $response = $middleware->handle($request, function () {});

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('auth/login', $response->getContent() ?: '');
    }

    #[Test]
    public function redirect_ke_login_jika_token_invalid()
    {
        ToolsHelper::setAuthToken('invalid-token');

        // Mock UserApi mengembalikan response tanpa user data
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('getMe')
            ->with('invalid-token')
            ->andReturn((object) [
                'data' => (object) [
                    // Tidak ada property 'user'
                ],
            ]);

        $request = Request::create('/app/profile', 'GET');
        $middleware = new CheckAuthMiddleware;

        $response = $middleware->handle($request, function () {});

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('auth/login', $response->getContent() ?: '');
    }

    #[Test]
    public function melanjutkan_request_dengan_auth_data_jika_token_valid()
    {
        $userData = (object) [
            'id' => '8357fda6-67f7-4a99-8f01-9847d6920599',
            'name' => 'Test User',
        ];

        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('getMe')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'user' => $userData,
                ],
            ]);

        // Mock HakAksesModel
        $hakAksesMock = Mockery::mock('alias:'.HakAksesModel::class);
        $hakAksesMock
            ->shouldReceive('where')
            ->with('user_id', $userData->id)
            ->once()
            ->andReturnSelf();
        $hakAksesMock
            ->shouldReceive('first')
            ->once()
            ->andReturn((object) ['akses' => 'view,edit']);

        // Buat request & middleware
        $request = Request::create('/app/profile', 'GET');
        $middleware = new CheckAuthMiddleware;

        // Jalankan handle()
        $response = $middleware->handle($request, function ($req) {
            // Assertion
            $auth = $req->attributes->get('auth');
            $this->assertEquals(['view', 'edit'], $auth->akses);

            return response('Success', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function melanjutkan_request_dengan_akses_kosong_jika_tidak_ada_hak_akses()
    {
        $userData = (object) [
            'id' => '8357fda6-67f7-4a99-8f01-9847d6920599',
            'name' => 'Test User',
        ];

        ToolsHelper::setAuthToken('valid-token');

        // Mock UserApi
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock
            ->shouldReceive('getMe')
            ->with('valid-token')
            ->andReturn((object) [
                'data' => (object) [
                    'user' => $userData,
                ],
            ]);

        // Mock HakAksesModel
        $hakAksesMock = Mockery::mock('alias:'.HakAksesModel::class);
        $hakAksesMock
            ->shouldReceive('where')
            ->with('user_id', $userData->id)
            ->once()
            ->andReturnSelf();
        $hakAksesMock
            ->shouldReceive('first')
            ->once()
            ->andReturnNull();

        $request = Request::create('/app/profile', 'GET');
        $middleware = new CheckAuthMiddleware;

        $response = $middleware->handle($request, function ($req) {
            $auth = $req->attributes->get('auth');
            $this->assertEquals([], $auth->akses);

            return response('Success', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }
}
