<?php

namespace Tests\Unit\Middleware;

use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use App\Http\Middleware\CheckAuthMiddleware;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response as LaravelResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        Mockery::close();

        if (!Schema::hasTable('user_id_mappings')) {
            Schema::create('user_id_mappings', function (Blueprint $table) {
                $table->id();
                $table->string('api_user_id')->index();
                $table->string('laravel_user_id')->index();
                $table->timestamps();
            });
        }

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

    /**
     * Helper untuk closure $next agar mengembalikan Response valid
     */
    private function getNextClosure()
    {
        return function ($request) {
            return new LaravelResponse('OK', 200);
        };
    }

    #[Test]
    public function jalur_mismatch_id_api_dan_laravel()
    {
        $token = 'mismatch-token';
        ToolsHelper::setAuthToken($token);

        $laravelUser = User::factory()->create([
            'id' => (string) Str::uuid(),
            'email' => 'mismatch@example.com'
        ]);

        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getMe')->andReturn((object) [
            'data' => (object) [
                'user' => (object) [
                    'id' => 'api-different-id',
                    'email' => 'mismatch@example.com',
                    'name' => 'Mismatch User'
                ]
            ]
        ]);

        $request = Request::create('/test', 'GET');
        $middleware = new CheckAuthMiddleware();

        // FIX: Pastikan closure mengembalikan response
        $response = $middleware->handle($request, $this->getNextClosure());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertDatabaseHas('user_id_mappings', [
            'api_user_id' => 'api-different-id',
            'laravel_user_id' => $laravelUser->id
        ]);
    }

    #[Test]
    public function jalur_buat_user_baru_jika_tidak_ditemukan()
    {
        $token = 'new-user-token';
        ToolsHelper::setAuthToken($token);

        $newApiId = 'new-uuid-999';
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getMe')->andReturn((object) [
            'data' => (object) [
                'user' => (object) [
                    'id' => $newApiId,
                    'email' => 'new@example.com',
                    'name' => 'New User',
                    'username' => 'newuser'
                ]
            ]
        ]);

        $request = Request::create('/test', 'GET');
        $middleware = new CheckAuthMiddleware();

        $response = $middleware->handle($request, $this->getNextClosure());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertDatabaseHas('users', ['id' => $newApiId]);
        $this->assertDatabaseHas('profiles', ['user_id' => $newApiId]);
    }

    #[Test]
    public function jalur_fallback_saat_gagal_buat_user_id_api()
    {
        // 1. Setup Token
        $token = 'fallback-token';
        ToolsHelper::setAuthToken($token);

        $apiUserId = 'id-api-gagal-123';
        
        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getMe')->andReturn((object) [
            'data' => (object) [
                'user' => (object) [
                    'id' => $apiUserId,
                    'email' => 'fallback-user@example.com',
                    'name' => 'Fallback User',
                    'username' => 'fallbackuser'
                ]
            ]
        ]);

        // 2. TEKNIK SABOTASE: Gunakan Event Listener
        // Kita paksa User::create pertama kali melempar Exception
        User::creating(function ($user) use ($apiUserId) {
            if ($user->id === $apiUserId) {
                throw new \Exception("Simulated Database Failure for Initial ID");
            }
        });

        $request = Request::create('/test', 'GET');
        $middleware = new CheckAuthMiddleware();

        // 3. Jalankan Middleware
        $response = $middleware->handle($request, function ($req) use ($apiUserId) {
            $laravelUser = $req->attributes->get('laravel_user');

            // VERIFIKASI: ID harus berbeda dan harus berformat UUID
            $this->assertNotEquals($apiUserId, $laravelUser->id);
            
            // Menggunakan regex jika Str::isUuid gagal mendeteksi cast string dari SQLite
            $isUuid = preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $laravelUser->id);
            $this->assertEquals(1, $isUuid, "ID {$laravelUser->id} bukan UUID yang valid");
            
            return new \Illuminate\Http\Response('OK', 200);
        });

        // 4. Pastikan data tersimpan di DB
        $this->assertDatabaseHas('users', ['email' => 'fallback-user@example.com']);
        $this->assertEquals(200, $response->getStatusCode());
        
        // Bersihkan listener agar tidak merusak test lain
        User::flushEventListeners();
    }

    #[Test]
    public function jalur_cari_via_mapping_table()
    {
        $token = 'mapping-token';
        ToolsHelper::setAuthToken($token);

        $laravelUser = User::factory()->create(['email' => 'mapped@example.com']);
        DB::table('user_id_mappings')->insert([
            'api_user_id' => 'api-external-id',
            'laravel_user_id' => $laravelUser->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getMe')->andReturn((object) [
            'data' => (object) [
                'user' => (object) [
                    'id' => 'api-external-id',
                    'name' => 'Mapped User'
                    // Tanpa email agar kueri awal (by email) gagal
                ]
            ]
        ]);

        $request = Request::create('/test', 'GET');
        $middleware = new CheckAuthMiddleware();

        $response = $middleware->handle($request, $this->getNextClosure());
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertDatabaseHas('user_id_mappings', [
            'api_user_id' => 'api-external-id',
            'laravel_user_id' => $laravelUser->id
        ]);
    }
#[Test]
    public function jalur_gagal_mapping_dan_fallback_ke_cache()
    {
        $token = 'cache-fallback-token';
        ToolsHelper::setAuthToken($token);
        $user = User::factory()->create();
        $apiId = 'api-error-99';

        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getMe')->andReturn((object) [
            'data' => (object) [
                'user' => (object) [
                    'id' => $apiId,
                    'email' => $user->email,
                    'name' => $user->name
                ]
            ]
        ]);

        // JANGAN DROP TABEL, tapi manipulasi agar INSERT gagal
        // Kita paksa table exist tapi kolomnya tidak cocok untuk memicu QueryException
        Schema::dropIfExists('user_id_mappings');
        Schema::create('user_id_mappings', function ($table) {
            $table->id(); 
            // Kita hilangkan kolom api_user_id agar INSERT di middleware melempar Exception
        });

        $request = Request::create('/test', 'GET');
        $middleware = new CheckAuthMiddleware();

        $response = $middleware->handle($request, $this->getNextClosure());

        $this->assertEquals(200, $response->getStatusCode());
        
        // Verifikasi: Karena kueri INSERT gagal, blok catch akan menyimpan ke cache
        $this->assertEquals($user->id, cache()->get('user_mapping_'.$apiId));
    }
    
    #[Test]
    public function jalur_update_mapping_jika_sudah_ada()
    {
        $token = 'update-mapping-token';
        ToolsHelper::setAuthToken($token);
        $user = User::factory()->create();

        // Masukkan data lama
        DB::table('user_id_mappings')->insert([
            'api_user_id' => 'api-exist',
            'laravel_user_id' => 'old-id',
            'created_at' => now(),
            'updated_at' => now()->subDays(1)
        ]);

        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getMe')->andReturn((object) [
            'data' => (object) [
                'user' => (object) [
                    'id' => 'api-exist',
                    'email' => $user->email,
                    'name' => $user->name
                ]
            ]
        ]);

        $request = Request::create('/test', 'GET');
        $middleware = new CheckAuthMiddleware();
        $middleware->handle($request, $this->getNextClosure());

        // Verifikasi: Laravel ID harus terupdate di tabel mapping
        $this->assertDatabaseHas('user_id_mappings', [
            'api_user_id' => 'api-exist',
            'laravel_user_id' => $user->id
        ]);
    }

    #[Test]
    public function jalur_hak_akses_kosong_saat_exception()
    {
        $token = 'akses-error-token';
        ToolsHelper::setAuthToken($token);
        $user = User::factory()->create();

        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getMe')->andReturn((object) [
            'data' => (object) [
                'user' => (object) [
                    'id' => 'api-id-error',
                    'email' => $user->email,
                    'name' => $user->name
                ]
            ]
        ]);

        // SABOTASE: Hapus tabel hak akses untuk memicu catch
        Schema::dropIfExists('m_hak_akses');

        $request = Request::create('/test', 'GET');
        $middleware = new CheckAuthMiddleware();

        $response = $middleware->handle($request, function($req) {
            $auth = $req->attributes->get('auth');
            // Verifikasi: akses harus array kosong [] jika catch terpanggil
            $this->assertIsArray($auth->akses);
            $this->assertEmpty($auth->akses);
            return new LaravelResponse('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function jalur_gagal_login_internal_laravel()
    {
        $token = 'login-fail-token';
        ToolsHelper::setAuthToken($token);
        $user = User::factory()->create();

        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getMe')->andReturn((object) [
            'data' => (object) [
                'user' => (object) ['id' => 'api-1', 'email' => $user->email]
            ]
        ]);

        // SABOTASE: Gunakan Mockery pada facade Auth untuk melempar error
        // Ini akan memicu catch (\Exception $e) pada bagian // 7. Login Laravel
        \Illuminate\Support\Facades\Auth::shouldReceive('check')->andReturn(false);
        \Illuminate\Support\Facades\Auth::shouldReceive('id')->andReturn(null);
        \Illuminate\Support\Facades\Auth::shouldReceive('login')->andThrow(new \Exception("Auth System Down"));

        $request = Request::create('/test', 'GET');
        $middleware = new CheckAuthMiddleware();
        
        $response = $middleware->handle($request, $this->getNextClosure());

        // Meskipun login gagal, middleware harus tetap lanjut (karena di-catch)
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function jalur_saat_tabel_mapping_tidak_ada_di_awal()
    {
        Schema::dropIfExists('user_id_mappings');
        
        $token = 'no-table-token';
        ToolsHelper::setAuthToken($token);
        $user = User::factory()->create();

        $userApiMock = Mockery::mock('alias:'.UserApi::class);
        $userApiMock->shouldReceive('getMe')->andReturn((object) [
            'data' => (object) [
                'user' => (object) ['id' => 'api-no-table', 'email' => $user->email]
            ]
        ]);

        $request = Request::create('/test', 'GET');
        $middleware = new CheckAuthMiddleware();
        
        $response = $middleware->handle($request, $this->getNextClosure());
        
        // Cek log (opsional) atau pastikan tidak crash
        $this->assertEquals(200, $response->getStatusCode());
    }
}