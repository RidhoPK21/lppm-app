<?php

namespace Tests\Feature\Controllers\Api;

use App\Http\Controllers\Api\DosenController;
use App\Models\HakAksesModel;
use Illuminate\Support\Facades\Route;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
// Import Attributes PHPUnit
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DosenControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Matikan Middleware agar fokus ke logic
        $this->withoutMiddleware();

        // 2. Daftarkan Route Manual untuk keperluan testing
        Route::get('/api/dosen-hak-akses', [DosenController::class, 'getDosenFromHakAkses']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Menggunakan Attribute untuk isolasi proses agar mocking 'overload'
     * tidak merusak factory di test file lain.
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function get_dosen_returns_success_data()
    {
        // ARRANGE: Siapkan Data Palsu
        $mockData = collect([
            (object) [
                'user_id' => 'dosen-001',
                'akses' => 'Dosen',
                'created_at' => now(),
            ],
            (object) [
                'user_id' => 'dosen-002',
                'akses' => 'Dosen',
                'created_at' => now(),
            ],
        ]);

        // MOCKING: Pura-pura menjadi HakAksesModel menggunakan overload
        $mock = Mockery::mock('overload:'.HakAksesModel::class);

        $mock->shouldReceive('where')
            ->once()
            ->with('akses', 'Dosen')
            ->andReturnSelf();

        $mock->shouldReceive('get')
            ->once()
            ->with(['user_id', 'akses', 'created_at'])
            ->andReturn($mockData);

        // ACT
        $response = $this->getJson('/api/dosen-hak-akses');

        // ASSERT
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data dosen berhasil diambil',
                'count' => 2,
            ]);

        $response->assertJsonFragment([
            'user_id' => 'dosen-001',
            'nama' => 'Dosen (dosen-001)',
            'email' => 'dosen-001@dosen.local',
            'is_invited' => false,
        ]);
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function get_dosen_returns_empty_if_no_data()
    {
        $mock = Mockery::mock('overload:'.HakAksesModel::class);

        $mock->shouldReceive('where')->once()->with('akses', 'Dosen')->andReturnSelf();
        $mock->shouldReceive('get')->once()->andReturn(collect([]));

        $response = $this->getJson('/api/dosen-hak-akses');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
                'count' => 0,
            ]);
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function get_dosen_handles_exceptions()
    {
        $mock = Mockery::mock('overload:'.HakAksesModel::class);

        $mock->shouldReceive('where')
            ->andThrow(new \Exception('Koneksi Database Putus!'));

        $response = $this->getJson('/api/dosen-hak-akses');

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Gagal mengambil data dosen',
                'count' => 0,
            ]);
    }
}
