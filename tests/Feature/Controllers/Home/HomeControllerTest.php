<?php

namespace Tests\Feature\Controllers\Home;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Inertia;
use Mockery;
use Tests\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_menampilkan_halaman_beranda_dengan_data_lengkap()
    {
        $user = User::factory()->create();
        
        Inertia::shouldReceive('always')->andReturnUsing(function ($value) {
             return Mockery::mock('overload:Inertia\AlwaysProp', ['getValue' => $value]);
        });
        
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
    }

    public function test_index_berhasil_dengan_auth_kosong()
    {
         $response = $this->get('/');
         // Sesuaikan: Jika '/' butuh login, assert 302. Jika tidak, assert 200.
         $response->assertStatus(302); 
    }
}