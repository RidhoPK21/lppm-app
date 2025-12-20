<?php

namespace Tests\Feature\Controllers\Home;

use App\Http\Controllers\App\Home\HomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Matikan middleware (auth, session, csrf) agar test fokus ke controller
        $this->withoutMiddleware();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function index_menampilkan_halaman_beranda_dengan_data_lengkap()
    {
        // 1. DEFINISI ROUTE MANUAL
        // Kita buat URL khusus '/tes-url-beranda' yang mengarah ke controller
        Route::get('/tes-url-beranda', [HomeController::class, 'index']);

        // 2. MOCK ToolsHelper
        // Kita palsukan Helper agar tidak error mengakses session/cookie
        $mockHelper = Mockery::mock('alias:App\Helper\ToolsHelper');
        $mockHelper->shouldReceive('getAuthToken')
            ->andReturn('token-palsu-123');

        // 3. JALANKAN REQUEST KE URL LANGSUNG
        // JANGAN pakai route('app.home'), tapi pakai URL yang kita buat di langkah 1.
        $response = $this->get('/tes-url-beranda');

        // 4. ASSERTION (Cek Hasil)
        $response->assertStatus(200);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('app/home/home-page')
            ->has('auth')
            ->has('pageName')
            ->where('authToken', 'token-palsu-123') // Pastikan token sesuai mock
        );
    }
}
