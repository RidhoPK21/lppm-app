<?php

namespace Tests\Unit\Middleware;

use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use App\Http\Middleware\CheckAuthMiddleware;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class CheckAuthMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Bersihkan mock sebelumnya
        Mockery::close();

        // 1. Buat tabel 'user_id_mappings' secara manual agar tidak error "no such table"
        if (!Schema::hasTable('user_id_mappings')) {
            Schema::create('user_id_mappings', function (Blueprint $table) {
                $table->id();
                $table->string('api_user_id')->index();
                $table->string('laravel_user_id')->index();
                $table->timestamps();
            });
        }

        // 2. Buat tabel 'm_hak_akses' secara manual
        if (!Schema::hasTable('m_hak_akses')) {
            Schema::create('m_hak_akses', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('user_id')->index();
                $table->text('akses');
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function redirect_ke_login_jika_token_tidak_ada()
    {
        // ARRANGE
        // Pastikan token kosong
        ToolsHelper::setAuthToken('');
        
        $request = Request::create('/test', 'GET');
        $middleware = new CheckAuthMiddleware();

        // ACT
        $response = $middleware->handle($request, function () {});

        // ASSERT
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('auth.login'), $response->getTargetUrl());
    }

    #[Test]
    public function redirect_ke_login_jika_token_invalid()
    {
        // ARRANGE
        ToolsHelper::setAuthToken('invalid-token');

        // Mock UserApi::getMe return object tapi tanpa data user
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getMe')
            ->with('invalid-token')
            ->andReturn((object) ['status' => 'error', 'data' => (object) []]);

        $request = Request::create('/test', 'GET');
        $middleware = new CheckAuthMiddleware();

        // ACT
        $response = $middleware->handle($request, function () {});

        // ASSERT
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('auth.login'), $response->getTargetUrl());
    }

    #[Test]
    public function melanjutkan_request_dengan_auth_data_jika_token_valid()
    {
        // ARRANGE
        $token = 'valid-token';
        ToolsHelper::setAuthToken($token);

        // Siapkan data user dari API
        $apiUserId = 'api-user-123';
        $email = 'test@example.com';
        
        // Buat user di DB lokal agar bisa login
        $user = User::factory()->create([
            'id' => $apiUserId,
            'email' => $email,
            'name' => 'Test User'
        ]);

        // Mock UserApi response
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getMe')
            ->with($token)
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) [
                    'user' => (object) [
                        'id' => $apiUserId,
                        'email' => $email,
                        'name' => 'Test User',
                        'username' => 'testuser'
                    ]
                ]
            ]);

        $request = Request::create('/test', 'GET');
        $middleware = new CheckAuthMiddleware();

        // ACT
        $response = $middleware->handle($request, function ($req) use ($apiUserId) {
            // ASSERT di dalam closure next
            $this->assertTrue($req->attributes->has('auth'));
            $this->assertEquals($apiUserId, $req->attributes->get('auth')->id);
            return new \Illuminate\Http\Response('OK');
        });

        // ASSERT response final
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    #[Test]
    public function melanjutkan_request_dengan_akses_kosong_jika_tidak_ada_hak_akses()
    {
        // ARRANGE
        $token = 'valid-token-no-access';
        ToolsHelper::setAuthToken($token);

        $apiUserId = 'user-no-access';
        $email = 'noaccess@example.com';

        // Buat user di DB
        User::factory()->create([
            'id' => $apiUserId,
            'email' => $email,
        ]);

        // Mock UserApi
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getMe')
            ->with($token)
            ->andReturn((object) [
                'status' => 'success',
                'data' => (object) [
                    'user' => (object) [
                        'id' => $apiUserId,
                        'email' => $email,
                        'name' => 'No Access User'
                    ]
                ]
            ]);

        // Pastikan tabel hak akses kosong atau user ini tidak punya record
        // (Default refresh database sudah membersihkan tabel)

        $request = Request::create('/test', 'GET');
        $middleware = new CheckAuthMiddleware();

        // ACT
        $response = $middleware->handle($request, function ($req) {
            $authData = $req->attributes->get('auth');
            // ASSERT akses array harus kosong
            $this->assertIsArray($authData->akses);
            $this->assertEmpty($authData->akses);
            
            return new \Illuminate\Http\Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }
}