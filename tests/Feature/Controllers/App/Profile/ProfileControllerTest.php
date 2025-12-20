<?php

namespace Tests\Feature\Controllers\App\Profile;

use App\Models\HakAksesModel;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ProfileControllerTest extends TestCase
{
    // Gunakan trait WithoutMiddleware agar tidak terlempar ke halaman login (Redirect 302)
    use RefreshDatabase, WithFaker, WithoutMiddleware;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat User dummy
        $userTemp = User::factory()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Dr. Jane Doe',
            'email' => 'jane.doe@example.com',
        ]);

        // 2. Beri hak akses (Opsional jika middleware dimatikan, tapi bagus untuk data integrity)
        HakAksesModel::factory()->create([
            'user_id' => $userTemp->id,
            'akses' => 'DOSEN',
        ]);

        $this->user = $userTemp;
        $this->actingAs($this->user);
    }

    /**
     * Test Update Profil - Harus menggunakan field yang sesuai dengan $request->validate di Controller
     */
    public function test_update_profile_via_http_successfully(): void
    {
        // ARRANGE: Pastikan profil sudah ada di DB (karena index/update menggunakan firstOrCreate/firstOrNew)
        Profile::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'nidn' => '1111111111',
            'prodi' => 'Informatika Lama',
        ]);

        // DATA: Gunakan PascalCase (NIDN, Prodi) sesuai dengan validate() di ProfileController Anda
        $updateData = [
            'NIDN' => '9876543210',
            'Prodi' => 'Teknik Elektro',
            'SintaID' => 'S123',
            'ScopusID' => 'SC456',
        ];

        // ACT: Kirim request POST ke route update
        // Jangan gunakan followingRedirects() terlebih dahulu agar kita bisa cek status 302 (back)
        $response = $this->post(route('app.profile.update'), $updateData);

        // ASSERT:
        // 1. Controller mengembalikan return back(), yang berarti status 302 (Redirect)
        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Profil akademik berhasil diperbarui!');

        // 2. Cek apakah data benar-benar tersimpan di database (mapping ke snake_case di model)
        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->user->id,
            'nidn' => '9876543210',
            'prodi' => 'Teknik Elektro',
            'sinta_id' => 'S123',
            'scopus_id' => 'SC456',
        ]);
    }

    /**
     * Test apakah halaman profil menampilkan data yang sudah di-merge (Auth + DB)
     */
    public function test_index_displays_merged_profile_data(): void
    {
        // ARRANGE: Buat data di tabel profiles
        Profile::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'name' => 'Nama di Profile',
            'nidn' => '123456',
            'prodi' => 'Sistem Informasi',
        ]);

        // ACT
        $response = $this->get(route('app.profile'));

        // ASSERT
        $response->assertStatus(200);

        // Cek apakah data yang dikirim ke Inertia (Props 'user') sudah benar
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Index')
            ->has('user', fn ($user) => $user
                ->where('name', $this->user->name) // Nama dari Auth User (Priority)
                ->where('NIDN', '123456')         // Nama field PascalCase untuk Frontend
                ->where('ProgramStudi', 'Sistem Informasi') // Mapping dari 'prodi' di merged data
                ->etc()
            )
        );
    }
}
