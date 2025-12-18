<?php

namespace Tests\Feature\Controllers\Api;

use App\Http\Controllers\Api\DosenController;
use App\Models\HakAksesModel;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class DosenControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Matikan Middleware (Auth/Sanctum) agar test fokus ke logic controller
        $this->withoutMiddleware();

        // 2. Daftarkan Route Manual (agar test tidak error jika route belum ada di api.php)
        Route::get('/api/dosen-hak-akses', [DosenController::class, 'getDosenFromHakAkses']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
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
            ]
        ]);

        // MOCKING: Pura-pura menjadi HakAksesModel
        $mock = Mockery::mock('overload:' . HakAksesModel::class);
        
        // Harapkan pemanggilan: where('akses', 'Dosen') -> get(...)
        $mock->shouldReceive('where')
             ->once()
             ->with('akses', 'Dosen')
             ->andReturnSelf(); // Return object builder agar bisa dichain
        
        $mock->shouldReceive('get')
             ->once()
             ->with(['user_id', 'akses', 'created_at'])
             ->andReturn($mockData); // Kembalikan koleksi data palsu di atas

        // ACT: Panggil endpoint
        $response = $this->getJson('/api/dosen-hak-akses');

        // ASSERT: Cek hasil
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Data dosen berhasil diambil',
                     'count' => 2,
                 ]);

        // Cek struktur data spesifik hasil transform map()
        $response->assertJsonFragment([
            'user_id' => 'dosen-001',
            'nama' => 'Dosen (dosen-001)', // Memastikan logic string interpolation jalan
            'email' => 'dosen-001@dosen.local',
            'is_invited' => false,
        ]);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function get_dosen_returns_empty_if_no_data()
    {
        // MOCKING: Return koleksi kosong
        $mock = Mockery::mock('overload:' . HakAksesModel::class);
        
        $mock->shouldReceive('where')->once()->with('akses', 'Dosen')->andReturnSelf();
        $mock->shouldReceive('get')->once()->andReturn(collect([]));

        // ACT
        $response = $this->getJson('/api/dosen-hak-akses');

        // ASSERT
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [],
                     'count' => 0,
                 ]);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function get_dosen_handles_exceptions()
    {
        // MOCKING: Simulasikan Error Database
        $mock = Mockery::mock('overload:' . HakAksesModel::class);
        
        $mock->shouldReceive('where')
             ->andThrow(new \Exception('Koneksi Database Putus!'));

        // ACT
        $response = $this->getJson('/api/dosen-hak-akses');

        // ASSERT: Harusnya masuk ke block catch() dan return 500
        $response->assertStatus(500)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Gagal mengambil data dosen',
                     'count' => 0,
                 ]);
    }
}