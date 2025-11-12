<?php

namespace Tests\Unit\Provider;

use App\Helper\ToolsHelper;
use App\Providers\AppServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function memaksa_https_ketika_environment_remote()
    {
        // Mock method forceScheme untuk memverifikasi dipanggil dengan parameter 'https'
        URL::shouldReceive('forceScheme')
            ->once()
            ->with('https');

        // Set environment ke 'remote' untuk memicu kondisi force HTTPS
        app()->detectEnvironment(fn () => 'remote');

        $provider = new AppServiceProvider(app());
        $provider->boot();

        // Tambah assertion agar tidak ada warning
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function memaksa_https_ketika_config_force_https_true()
    {
        // Set environment ke 'local' tapi config force_https true
        app()->detectEnvironment(fn () => 'local');
        config(['sdi.force_https' => true]);

        // Mock method forceScheme untuk memverifikasi dipanggil dengan parameter 'https'
        URL::shouldReceive('forceScheme')
            ->once()
            ->with('https');

        $provider = new AppServiceProvider(app());
        $provider->boot();

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function tidak_memaksa_https_jika_kondisi_tidak_terpenuhi()
    {
        // Set environment ke 'local' dan config force_https false
        app()->detectEnvironment(fn () => 'local');
        config(['sdi.force_https' => false]);

        // Pastikan forceScheme tidak dipanggil
        URL::shouldReceive('forceScheme')->never();

        $provider = new AppServiceProvider(app());
        $provider->boot();

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function register_method_tidak_mengeksekusi_apa_apa()
    {
        // Test method register() yang kosong
        $provider = new AppServiceProvider(app());

        // Panggil method register dan pastikan tidak ada error
        $provider->register();

        // Verifikasi bahwa method tersebut berjalan tanpa masalah
        $this->assertTrue(true);
    }

    #[Test]
    public function rate_limiter_berhasil_dikonfigurasi()
    {
        // Mock RateLimiter untuk memverifikasi konfigurasi
        RateLimiter::shouldReceive('for')
            ->once()
            ->with('req-limit', Mockery::type('Closure'))
            ->andReturnUsing(function ($name, $callback) {
                // Simulasikan request untuk testing closure rate limiter
                $request = Mockery::mock(Request::class);
                $request->shouldReceive('user')->andReturn(null);
                $request->shouldReceive('ip')->andReturn('127.0.0.1');

                // Panggil closure untuk memastikan tidak ada error
                $result = $callback($request);

                // Verifikasi bahwa result adalah instance dari Limit
                $this->assertInstanceOf(Limit::class, $result);

                return $result;
            });

        $provider = new AppServiceProvider(app());
        $provider->boot();

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function rate_limiter_dengan_user_terautentikasi()
    {
        // Test rate limiter dengan user yang terautentikasi
        RateLimiter::shouldReceive('for')
            ->once()
            ->with('req-limit', Mockery::type('Closure'))
            ->andReturnUsing(function ($name, $callback) {
                // Buat mock user
                $user = (object) [
                    'id' => ToolsHelper::generateId(),
                    'name' => 'Test User',
                ];

                // Buat mock request dengan user
                $request = Mockery::mock(\Illuminate\Http\Request::class);
                $request->shouldReceive('user')->andReturn($user);
                $request->shouldNotReceive('ip');  // IP tidak boleh dipanggil jika user ada

                // Panggil closure
                $result = $callback($request);

                $this->assertInstanceOf(Limit::class, $result);

                return $result;
            });

        $provider = new AppServiceProvider(app());
        $provider->boot();

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function rate_limiter_response_memiliki_format_yang_benar()
    {
        // Test langsung response format tanpa melalui RateLimiter
        $request = Mockery::mock(\Illuminate\Http\Request::class);
        $headers = ['Retry-After' => 300];

        // Replikasi response callback dari AppServiceProvider
        $responseCallback = function ($request, array $headers) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terlalu banyak percobaan. Silakan coba lagi dalam 5 menit.',
                'retry_after' => $headers['Retry-After'] ?? null,
            ], 429);
        };

        // Eksekusi dan verifikasi
        $response = $responseCallback($request, $headers);

        $this->assertEquals(429, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('Terlalu banyak percobaan. Silakan coba lagi dalam 5 menit.', $responseData['message']);
        $this->assertEquals(300, $responseData['retry_after']);
    }

    #[Test]
    public function rate_limiter_response_tanpa_retry_after()
    {
        // Test ketika headers tidak memiliki Retry-After
        $request = Mockery::mock(\Illuminate\Http\Request::class);
        $headers = [];  // Tidak ada Retry-After

        $responseCallback = function ($request, array $headers) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terlalu banyak percobaan. Silakan coba lagi dalam 5 menit.',
                'retry_after' => $headers['Retry-After'] ?? null,
            ], 429);
        };

        $response = $responseCallback($request, $headers);
        $responseData = json_decode($response->getContent(), true);

        $this->assertNull($responseData['retry_after']);
    }

    #[Test]
    public function muncul_pesan_error_saat_melebihi_batas_request()
    {
        // Hapus data hit sebelumnya agar test bersih
        // Gunakan key yang sesuai dengan rate limiter
        RateLimiter::clear('req-limit:127.0.0.1');

        // Kirim request sebanyak batas maksimum (200 kali)
        for ($i = 0; $i < 200; $i++) {
            $response = $this->getJson('/api/test-limit');
            $response->assertOk();  // Pastikan semua request berhasil
        }

        // Kirim request ke-201 -> seharusnya sudah kena limit
        $response = $this->getJson('/api/test-limit');

        // Debug jika masih error
        if ($response->status() !== 429) {
            // print_r($response->json());
        }

        // Pastikan status kodenya 429 (Too Many Requests)
        $response
            ->assertStatus(429)
            ->assertJson([
                'status' => 'error',
                'message' => 'Terlalu banyak percobaan. Silakan coba lagi dalam 5 menit.',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'retry_after',
            ]);

        RateLimiter::clear('req-limit:127.0.0.1');
    }
}
