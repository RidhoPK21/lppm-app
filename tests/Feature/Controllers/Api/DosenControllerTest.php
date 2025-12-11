<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DosenControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // FIX: Definisikan guard 'sanctum' ke driver session agar actingAs() berjalan
        Config::set('auth.guards.sanctum', [
            'driver' => 'session',
            'provider' => 'users',
        ]);
    }

    public function test_get_dosen_from_hak_akses_returns_json()
    {
        $user = User::factory()->create();
        
        // Gunakan guard 'sanctum' yang sudah kita mock
        $response = $this->actingAs($user, 'sanctum')
                         ->get('/hakakses/dosen');

        $response->assertStatus(200);
    }

    public function test_get_dosen_unauthorized_if_not_logged_in()
    {
        $response = $this->get('/hakakses/dosen');
        // Assert redirect ke login (302) karena token tidak ada
        $response->assertStatus(302);
    }
}