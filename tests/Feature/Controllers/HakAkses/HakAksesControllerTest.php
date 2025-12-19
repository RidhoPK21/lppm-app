<?php

namespace Tests\Feature\Controllers\HakAkses;

use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use App\Http\Controllers\App\HakAkses\HakAksesController;
use App\Models\HakAksesModel;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class HakAksesControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Membuat tabel m_hak_akses secara manual jika belum ada di database testing
        if (! Schema::hasTable('m_hak_akses')) {
            Schema::create('m_hak_akses', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('user_id')->index();
                $table->text('akses');
                $table->timestamps();

                // Jika database testing mendukung foreign key, tambahkan ini:
                // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        Mockery::close();

        // Mock Inertia agar tidak error saat pemanggilan global
        Inertia::shouldReceive('always')
            ->zeroOrMoreTimes()
            ->andReturnUsing(fn ($value) => $value);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function index_redirect_ke_home_jika_bukan_editor()
    {
        $auth = (object) ['akses' => ['User'], 'roles' => ['User']];
        $request = Request::create('/hak-akses', 'GET');
        $request->attributes->set('auth', $auth);

        $controller = new HakAksesController;
        $response = $controller->index($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('home'), $response->getTargetUrl());
    }

    #[Test]
    public function index_menampilkan_halaman_jika_editor()
    {
        $auth = (object) ['akses' => ['Admin'], 'roles' => []];
        $request = Request::create('/hak-akses', 'GET');
        $request->attributes->set('auth', $auth);

        // FIX: Buat user asli untuk menghindari Foreign Key Violation
        $user = User::factory()->create(['id' => 'user1']);

        HakAksesModel::create([
            'id' => 'uuid-1',
            'user_id' => $user->id,
            'akses' => 'Admin',
        ]);

        // Mock UserApi
        $userApiMock = Mockery::mock('alias:' . UserApi::class);
        $userApiMock->shouldReceive('postReqUsersByIds')
            ->andReturn((object) [
                'data' => (object) [
                    'users' => [(object) ['id' => $user->id, 'name' => 'Test User']],
                ],
            ]);

        // Mock ToolsHelper Token
        $toolsMock = Mockery::mock('alias:' . ToolsHelper::class);
        $toolsMock->shouldReceive('getAuthToken')->andReturn('fake-token');

        // Mock Inertia Response
        $mockResponse = Mockery::mock(Response::class);
        $capturedProps = [];
        
        Inertia::shouldReceive('render')
            ->once()
            ->with('App/HakAkses/Index', Mockery::capture($capturedProps))
            ->andReturn($mockResponse);

        $controller = new HakAksesController;
        $response = $controller->index($request);

        $this->assertSame($mockResponse, $response);
        $this->assertCount(1, $capturedProps['aksesList']);
    }

    #[Test]
    public function post_change_berhasil_memperbarui_hak_akses()
    {
        $auth = (object) ['akses' => ['Admin'], 'roles' => []];
        
        // FIX: Buat user asli
        $user = User::factory()->create(['id' => 'user123']);

        $request = Request::create('/hak-akses/change', 'POST', [
            'userId' => $user->id,
            'hakAkses' => ['read', 'write'],
        ]);
        $request->attributes->set('auth', $auth);

        // Setup data awal
        HakAksesModel::create([
            'id' => 'old-id',
            'user_id' => $user->id,
            'akses' => 'old_access',
        ]);

        $controller = new HakAksesController;
        $response = $controller->postChange($request);

        $this->assertEquals(302, $response->getStatusCode());

        // Cek Database
        $this->assertDatabaseMissing('m_hak_akses', ['akses' => 'old_access']);
        $this->assertDatabaseHas('m_hak_akses', [
            'user_id' => $user->id,
            'akses' => 'read,write',
        ]);
    }

    #[Test]
    public function post_delete_berhasil_menghapus_hak_akses()
    {
        $auth = (object) ['akses' => ['Admin'], 'roles' => []];
        
        // FIX: Buat user asli
        $user = User::factory()->create(['id' => 'user123']);

        $request = Request::create('/hak-akses/delete', 'POST', [
            'userId' => $user->id,
        ]);
        $request->attributes->set('auth', $auth);

        HakAksesModel::create(['id' => 'del-1', 'user_id' => $user->id, 'akses' => 'test']);

        $controller = new HakAksesController;
        $response = $controller->postDelete($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseMissing('m_hak_akses', ['user_id' => $user->id]);
    }

    #[Test]
    public function post_delete_selected_berhasil_menghapus_hak_akses_terpilih()
    {
        $auth = (object) ['akses' => ['Admin'], 'roles' => []];
        
        // FIX: Buat user asli
        $u1 = User::factory()->create(['id' => 'user1']);
        $u2 = User::factory()->create(['id' => 'user2']);
        $u3 = User::factory()->create(['id' => 'user3']);

        $request = Request::create('/hak-akses/delete-selected', 'POST', [
            'userIds' => [$u1->id, $u2->id],
        ]);
        $request->attributes->set('auth', $auth);

        HakAksesModel::create(['id' => '1', 'user_id' => $u1->id, 'akses' => 'a']);
        HakAksesModel::create(['id' => '2', 'user_id' => $u2->id, 'akses' => 'b']);
        HakAksesModel::create(['id' => '3', 'user_id' => $u3->id, 'akses' => 'c']);

        $controller = new HakAksesController;
        $response = $controller->postDeleteSelected($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseMissing('m_hak_akses', ['user_id' => $u1->id]);
        $this->assertDatabaseMissing('m_hak_akses', ['user_id' => $u2->id]);
        $this->assertDatabaseHas('m_hak_akses', ['user_id' => $u3->id]);
    }

    #[Test]
    public function post_change_redirect_back_jika_bukan_editor()
    {
        $auth = (object) ['akses' => ['User'], 'roles' => ['User']];
        $request = Request::create('/hak-akses/change', 'POST');
        $request->attributes->set('auth', $auth);

        $controller = new HakAksesController;
        $response = $controller->postChange($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('Anda tidak memiliki izin untuk mengubah hak akses.', $response->getSession()->get('error'));
    }

    #[Test]
    public function post_delete_redirect_back_jika_bukan_editor()
    {
        $auth = (object) ['akses' => ['User'], 'roles' => ['User']];
        $request = Request::create('/hak-akses/delete', 'POST');
        $request->attributes->set('auth', $auth);

        $controller = new HakAksesController;
        $response = $controller->postDelete($request);

        $this->assertEquals(302, $response->getStatusCode());
    }

    #[Test]
    public function post_delete_selected_redirect_back_jika_bukan_editor()
    {
        $auth = (object) ['akses' => ['User'], 'roles' => ['User']];
        $request = Request::create('/hak-akses/delete-selected', 'POST');
        $request->attributes->set('auth', $auth);

        $controller = new HakAksesController;
        $response = $controller->postDeleteSelected($request);

        $this->assertEquals(302, $response->getStatusCode());
    }
}