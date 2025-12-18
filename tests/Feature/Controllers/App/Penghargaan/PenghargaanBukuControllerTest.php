<?php

namespace Tests\Feature\Controllers\App\Penghargaan;

use App\Models\BookAuthor;
use App\Models\BookSubmission;
use App\Models\Profile;
use App\Models\HakAksesModel; 
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Inertia;
use Mockery;
use Tests\TestCase;

// FIX UTAMA: Import Trait WithoutMiddleware
use Illuminate\Foundation\Testing\WithoutMiddleware; 

class PenghargaanBukuControllerTest extends TestCase
{
    // Panggil Trait WithoutMiddleware di sini
    use RefreshDatabase, WithFaker, WithoutMiddleware; 

    // Deklarasi properti (bisa nullable atau tidak, pola lokal akan menjaminnya)
    protected $user; 

    protected function setUp(): void
    {
        parent::setUp();

        // FIX MOCKERY: Bersihkan Mockery sebelum setup (mengatasi masalah dari test lain)
        Mockery::close();

        Storage::fake('public');

        // Mock PDF Facade
        Pdf::shouldReceive('loadView')
            ->zeroOrMoreTimes()
            ->andReturnSelf()
            ->shouldReceive('output')
            ->zeroOrMoreTimes()
            ->andReturn('mocked-pdf-content');

        // 🔥 POLA PERBAIKAN: Gunakan variabel lokal ($userTemp)
        $userTemp = User::factory()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Prof. Dr. Dosen',
            'email' => 'dosen@example.com',
        ]);

        // 2. Buat Hak Akses (menggunakan $userTemp)
        HakAksesModel::factory()->create([
            'user_id' => $userTemp->id,
            'akses' => 'DOSEN,Admin,Lppm Ketua', 
        ]);
        
        // 3. Buat Profile (menggunakan $userTemp)
        Profile::factory()->create([
            'user_id' => $userTemp->id,
            'name' => 'Prof. Dr. Dosen',
            'nidn' => '1234567890',
            'prodi' => 'Teknik Informatika',
            'sinta_id' => '1',
            'scopus_id' => '2',
        ]);

        // Tetapkan properti $this hanya di akhir setUp
        $this->user = $userTemp;

        // 4. Autentikasi User
        $this->actingAs($this->user);
    }
    
    // FIX MOCKERY: Tambahkan tearDown() untuk membersihkan Mockery setelah SETIAP test
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


    // --- TEST INDEX ---
    public function test_index_displays_mapped_user_submissions(): void
    {
        // ARRANGE
        $book1 = BookSubmission::factory()->create(['user_id' => $this->user->id, 'title' => 'Buku A', 'status' => 'SUBMITTED', 'book_type' => 'REFERENCE', 'created_at' => now()->subDay()]);
        BookAuthor::factory()->create(['book_submission_id' => $book1->id, 'name' => 'Penulis 1', 'role' => 'FIRST']);
        
        $book2 = BookSubmission::factory()->create(['user_id' => $this->user->id, 'title' => 'Buku B', 'status' => 'DRAFT', 'book_type' => 'MONOGRAPH', 'created_at' => now()]);
        BookAuthor::factory()->create(['book_submission_id' => $book2->id, 'name' => 'Penulis 2', 'role' => 'FIRST']);


        // Act
        $response = $this->get(route('app.penghargaan.buku.index'));

        // Assert
        $response->assertStatus(200)
                 ->assertInertia(fn (Inertia $page) => $page
                     ->component('app/penghargaan/buku/page')
                     ->has('buku', 2)
                     ->where('buku.0.judul', 'Buku B')
                 );
    }

    // --- TEST STORE ---
    public function test_store_submission_and_authors_successfully(): void
    {
        $requestData = [
            'judul' => 'Judul Buku Baru',
            'penulis' => 'Anggota 1, Anggota 2',
            'penerbit' => 'Penerbit Fiksi',
            'tahun' => date('Y'),
            'isbn' => '978-602-03-3277-5',
            'kategori' => 'REFERENCE',
            'jumlah_halaman' => 200,
            'level_penerbit' => 'INTERNATIONAL',
        ];

        // Act
        $response = $this->post(route('app.penghargaan.buku.store'), $requestData);

        // Assert DB
        $this->assertDatabaseHas('book_submissions', [
            'user_id' => $this->user->id,
            'title' => 'Judul Buku Baru',
            'status' => 'DRAFT',
        ]);

        // Mengambil submission yang baru dibuat
        $submission = BookSubmission::where('title', 'Judul Buku Baru')->first();
        
        // Assert Redirect
        $response->assertStatus(302)
                 ->assertRedirect(route('app.penghargaan.buku.upload', ['id' => $submission->id]));
    }

    public function test_store_fails_with_invalid_data(): void
    {
        $requestData = [
            'judul' => '',
            'penulis' => '',
            'tahun' => 1800,
            'jumlah_halaman' => 30,
            'level_penerbit' => 'INVALID_LEVEL',
            'kategori' => 'TEACHING'
        ];

        // Act
        $response = $this->post(route('app.penghargaan.buku.store'), $requestData);

        // Assert Validation Errors
        $response->assertStatus(302)
                 ->assertSessionHasErrors(['judul', 'penulis', 'tahun', 'jumlah_halaman', 'level_penerbit']);
        
        $this->assertDatabaseCount('book_submissions', 0);
    }

    // --- TEST SUBMIT ---
    public function test_submit_fails_if_status_is_not_draft(): void
    {
        // ARRANGE
        $submission = BookSubmission::factory()->create(['user_id' => $this->user->id, 'status' => 'VERIFIED_STAFF']);
        
        // Act
        $response = $this->post(route('app.penghargaan.buku.submit', $submission->id));

        // Assert
        $response->assertStatus(302)
                 ->assertSessionHas('error', 'Pengajuan sudah dikirim atau diproses.');
    }

    public function test_submit_fails_if_links_are_incomplete(): void
    {
        // ARRANGE
        $links = ['https://link.drive/1', 'https://link.drive/2']; // Hanya 2 link (kurang dari 5)
        $submission = BookSubmission::factory()->create(['user_id' => $this->user->id, 'status' => 'DRAFT', 'drive_link' => json_encode($links)]);
        
        // Act
        $response = $this->post(route('app.penghargaan.buku.submit', $submission->id));

        // Assert
        $expectedErrorMessage = 'Dokumen belum lengkap. Harap lengkapi semua link dokumen sebelum mengirim.';
        $response->assertStatus(302)
                 ->assertSessionHas('error', $expectedErrorMessage);
    }
    
    // 🔥 Method ensureHakAksesFactoryExists dihapus
}