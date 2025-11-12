<?php

namespace Tests\Feature\Livewire\Beranda;

use App\Livewire\Beranda\BerandaLivewire;
use Illuminate\Http\Request;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BerandaLivewireTest extends TestCase
{
    #[Test]
    public function mount_mengatur_auth_user_dari_request()
    {
        // Siapkan request dengan properti auth
        $request = Request::create('/app/beranda', 'GET');
        $request->auth = (object) ['id' => '123', 'name' => 'Test User'];
        $this->app->instance('request', $request);

        // Jalankan komponen
        $component = new BerandaLivewire();
        $component->mount();

        // Pastikan authUser terisi dari request
        $this->assertEquals($request->auth, $component->auth);
    }

    #[Test]
    public function render_mengembalikan_view_yang_benar()
    {
        // Jalankan komponen menggunakan Livewire test helper
        Livewire::test(BerandaLivewire::class)
            ->assertViewIs('features.beranda.beranda-livewire');
    }
}
