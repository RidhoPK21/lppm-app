<?php

namespace Tests\Feature\Controllers\App\Penghargaan;

use App\Models\BookAuthor;
use App\Models\BookSubmission;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Inertia;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class PenghargaanBukuControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $notificationMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        Storage::fake('local');

        $this->user = User::factory()->create();
        // 🔥 MENUTUP BARIS 224..232 & 230: Detail Profil
        Profile::create([
            'user_id' => $this->user->id,
            'name' => 'Dosen Pengaju',
            'nidn' => '112233',
            'prodi' => 'Informatika',
            'sinta_id' => 'S123',
            'scopus_id' => 'SC456',
        ]);

        $this->notificationMock = \Mockery::mock('alias:App\Http\Controllers\App\Notifikasi\NotificationController');
        $this->notificationMock->shouldReceive('sendBookSubmissionNotification')->andReturn(true)->byDefault();

        $this->actingAs($this->user);
    }

    private function createFullBook($attributes = [])
    {
        return BookSubmission::create(array_merge([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'title' => 'Buku Test Coverage',
            'isbn' => '123-456',
            'publication_year' => 2025,
            'publisher' => 'Del Press',
            'publisher_level' => 'NATIONAL',
            'book_type' => 'REFERENCE',
            'total_pages' => 100,
            'status' => 'DRAFT',
            'drive_link' => json_encode(['l1', 'l2', 'l3', 'l4', 'l5']),
            // 'pdf_path' => 'pdfs/book-submissions/test.pdf'
            'pdf_path' => null,
        ], $attributes));
    }

    /**
     * Menutup baris 510-520 (Download PDF Error Handling)
     */
    #[Test]
    public function test_download_pdf_not_found_handling()
    {
        // Beri path di DB, tapi JANGAN buat filenya di Storage::fake agar exists() return false
        $book = $this->createFullBook(['pdf_path' => 'file_hilang.pdf']);

        // Gunakan nama rute yang benar sesuai route:list Anda
        $response = $this->get(route('app.penghargaan.buku.download-pdf', $book->id));

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'File PDF tidak ditemukan di server.');
    }

    /**
     * Menutup baris 330-360 (Store Upload & PDF Generation)
     */
    #[Test]
    public function test_store_upload_generates_pdf_successfully()
    {
        $book = $this->createFullBook(['pdf_path' => null]);

        $response = $this->post(route('app.penghargaan.buku.store-upload', $book->id), [
            'links' => ['http://a.com', 'http://b.com', 'http://c.com', 'http://d.com', 'http://e.com'],
        ]);

        $book->refresh();
        $response->assertRedirect();

        $this->assertNotNull($book->pdf_path);
        // Cek apakah file ada di disk local dengan prefix public/ (karena itu yang ditulis controller)
        Storage::disk('local')->assertExists('public/'.$book->pdf_path);
    }

    /**
     * Menutup baris 400-450 (Preview & Regenerate PDF)
     */
    #[Test]
    public function test_preview_pdf_regeneration()
    {
        // Buat buku dengan path yang salah untuk memicu regeneration
        $book = $this->createFullBook(['pdf_path' => 'invalid/path.pdf']);

        $response = $this->get(route('app.penghargaan.buku.preview-pdf', $book->id));

        $response->assertStatus(200);
        $this->assertNotNull($book->fresh()->pdf_path);
    }

    /**
     * Menutup sisa mapping (Baris 530-550)
     */
    #[Test]
    public function test_index_mapping_full_coverage()
    {
        $this->createFullBook(['status' => 'PAID', 'book_type' => 'MONOGRAPH']);

        $response = $this->get(route('app.penghargaan.buku.index'));

        $response->assertInertia(fn (Inertia $page) => $page
            ->has('buku.0', fn ($page) => $page
                ->where('status', 'Selesai (Cair)')
                ->where('kategori', 'Monograf')
                ->etc()
            )
        );
    }

    /**
     * Menutup baris submit (Baris 364+)
     */
    #[Test]
    public function test_submit_success_flow()
    {
        $book = $this->createFullBook(['status' => 'DRAFT']);

        $response = $this->post(route('app.penghargaan.buku.submit', $book->id));

        $response->assertRedirect(route('app.penghargaan.buku.index'));
        $this->assertEquals('SUBMITTED', $book->fresh()->status);
    }

    #[Test]
    public function test_store_with_multiple_authors_and_whitespace()
    {
        $payload = [
            'judul' => 'Buku Riset',
            'penulis' => 'Penulis Satu, , Penulis Dua', // Empty space to trigger !empty check
            'penerbit' => 'Del Press',
            'tahun' => 2025,
            'isbn' => 'ISBN-XYZ',
            'kategori' => 'MONOGRAPH',
            'jumlah_halaman' => 45,
            'level_penerbit' => 'NATIONAL',
        ];

        $this->post(route('app.penghargaan.buku.store'), $payload)->assertRedirect();

        $this->assertDatabaseHas('book_authors', ['name' => 'Penulis Satu']);
        $this->assertDatabaseHas('book_authors', ['name' => 'Penulis Dua']);
    }

    /** * Menutup baris 430-487 (Preview & Regeneration)
     */
    #[Test]
    public function test_preview_pdf_regeneration_if_file_missing()
    {
        $book = $this->createFullBook(['pdf_path' => 'file_tidak_ada.pdf']);

        $response = $this->get(route('app.penghargaan.buku.preview-pdf', $book->id));

        $response->assertStatus(200);
        $this->assertNotNull($book->fresh()->pdf_path);
        $this->assertNotEquals('file_tidak_ada.pdf', $book->fresh()->pdf_path);
    }

    /** * Menutup baris 511-527 (Download Fail & Label Mapping)
     */
    #[Test]
    public function test_download_fail_and_status_label_mapping()
    {
        // 1. Test Download Fail (Baris 511-515)
        $book = $this->createFullBook(['pdf_path' => 'missing.pdf']);
        $this->get(route('app.penghargaan.buku.download-pdf', $book->id))
            ->assertSessionHas('error', 'File PDF tidak ditemukan di server.');

        // 2. Test Mapping Match (Baris 524-527)
        // Kita hapus semua buku dulu agar index 0 pasti buku ini
        BookSubmission::query()->delete();
        $this->createFullBook(['status' => 'PAID', 'book_type' => 'CHAPTER']);

        $response = $this->get(route('app.penghargaan.buku.index'));

        $response->assertInertia(fn (Inertia $page) => $page
            ->has('buku.0', fn ($page) => $page
                ->where('status', 'Selesai (Cair)')
                ->where('kategori', 'Book Chapter')
                ->etc()
            )
        );
    }

    /** * Menutup baris 291-319 (Submit Logic)
     */
    #[Test]
    public function test_submit_logic_branches()
    {
        // Bukan Draft
        $bookA = $this->createFullBook(['status' => 'SUBMITTED']);
        $this->post(route('app.penghargaan.buku.submit', $bookA->id))
            ->assertSessionHas('error', 'Pengajuan sudah dikirim atau diproses.');

        // Link Kurang dari 5
        $bookB = $this->createFullBook(['drive_link' => json_encode(['l1', '', '', '', ''])]);
        $this->post(route('app.penghargaan.buku.submit', $bookB->id))
            ->assertSessionHas('error', 'Dokumen belum lengkap. Harap lengkapi semua link dokumen sebelum mengirim.');
    }

    /** * Menutup baris 333-337 (Upload Docs)
     */
    #[Test]
    public function test_store_upload_flow()
    {
        $book = $this->createFullBook(['pdf_path' => null]);

        $this->post(route('app.penghargaan.buku.store-upload', $book->id), [
            'links' => ['https://d.com/1', 'https://d.com/2', 'https://d.com/3', 'https://d.com/4', 'https://d.com/5'],
        ])->assertRedirect();

        $this->assertDatabaseHas('submission_logs', ['action' => 'UPLOAD_DOCUMENTS']);
    }

    #[Test]
    public function test_store_handles_author_loop_and_empty_strings()
    {
        $payload = [
            'judul' => 'Buku Riset Baru',
            'penulis' => 'Penulis 1, , Penulis 2 ',
            'penerbit' => 'Penerbit IT Del',
            'tahun' => 2025,
            'isbn' => '978-123',
            'kategori' => 'REFERENCE',
            'jumlah_halaman' => 120,
            'level_penerbit' => 'INTERNATIONAL',
        ];

        $this->post(route('app.penghargaan.buku.store'), $payload)->assertRedirect();

        // Assert: 1 (Dosen Pengaju) + 2 (Dari string penulis) = 3
        $this->assertEquals(3, BookAuthor::count());
        $this->assertDatabaseHas('book_authors', ['name' => 'Dosen Pengaju (Anda)', 'role' => 'FIRST']);
        $this->assertDatabaseHas('book_authors', ['name' => 'Penulis 1', 'role' => 'MEMBER']);
    }

    /** * Menutup Baris 511-527: Download Fail & Mapping Match
     */
    #[Test]
    public function test_download_and_index_mappings()
    {
        // 1. Download Fail (Baris 511-515)
        $book = $this->createFullBook(['pdf_path' => 'file_hilang.pdf']);
        $this->get(route('app.penghargaan.buku.download-pdf', $book->id))
            ->assertSessionHas('error', 'File PDF tidak ditemukan di server.');

        // 2. Mapping Match (Baris 524-527)
        // Bersihkan DB agar index 0 pasti buku ini
        BookSubmission::query()->delete();
        $this->createFullBook(['status' => 'VERIFIED_STAFF', 'book_type' => 'MONOGRAPH']);

        $response = $this->get(route('app.penghargaan.buku.index'));
        $response->assertInertia(fn (Inertia $page) => $page
            ->has('buku.0', fn ($page) => $page
                ->where('status', 'Menunggu Review Ketua')
                ->where('kategori', 'Monograf')
                ->etc()
            )
        );
    }

    /** * Menutup Baris 430-487: Preview PDF Regeneration
     */
    #[Test]
    public function test_preview_pdf_regeneration_flow()
    {
        $book = $this->createFullBook(['pdf_path' => 'old/path.pdf']);

        // File tidak ada di Storage::fake('local'), memicu regeneration
        $response = $this->get(route('app.penghargaan.buku.preview-pdf', $book->id));

        $response->assertStatus(200);
        $this->assertNotNull($book->fresh()->pdf_path);
        $this->assertNotEquals('old/path.pdf', $book->fresh()->pdf_path);
    }

    /** * Menutup Baris 291-319: Submit Validation
     */
    #[Test]
    public function test_submit_validation_branches()
    {
        $book = $this->createFullBook(['status' => 'DRAFT', 'drive_link' => json_encode(['l1'])]);

        // Kasus: Link tidak lengkap (< 5)
        $this->post(route('app.penghargaan.buku.submit', $book->id))
            ->assertSessionHas('error', 'Dokumen belum lengkap. Harap lengkapi semua link dokumen sebelum mengirim.');
    }

    /**
     * Menutup Baris 179-185: Catch block pada store
     */
    /**
     * Menutup baris 179-185: Catch block pada store
     */
    #[Test]
    public function test_store_catch_block_on_error()
    {
        // 1. Siapkan data valid agar lolos validator
        $payload = [
            'judul' => 'Buku Pemicu Catch',
            'penulis' => 'Penulis X',
            'penerbit' => 'Penerbit Y',
            'tahun' => 2025,
            'isbn' => '999-ERROR',
            'kategori' => 'REFERENCE',
            'jumlah_halaman' => 55,
            'level_penerbit' => 'NATIONAL',
        ];

        // 2. Trik: Gunakan DB Transaction yang sengaja di-failkan lewat mock DB::commit
        // Kita tidak me-mock seluruh DB, hanya method commit agar melempar Exception
        // saat controller mencoba menyelesaikan transaksi.
        \Illuminate\Support\Facades\DB::shouldReceive('beginTransaction')->once();
        \Illuminate\Support\Facades\DB::shouldReceive('rollback')->once();
        \Illuminate\Support\Facades\DB::shouldReceive('commit')
            ->once()
            ->andThrow(new \Exception('Simulated Transaction Failure'));

        $response = $this->post(route('app.penghargaan.buku.store'), $payload);

        // 3. Verifikasi alur masuk ke catch
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['error']);

        $errorMessage = session('errors')->get('error')[0];
        $this->assertStringContainsString('Gagal menyimpan data', $errorMessage);
    }

    #[Test]
    public function test_store_comprehensive_logic()
    {
        $payload = [
            'judul' => 'Buku Riset',
            'penulis' => 'Penulis A, Penulis B', // Memicu loop explode
            'penerbit' => 'Erlangga',
            'tahun' => 2025,
            'isbn' => '999-XYZ',
            'kategori' => 'MONOGRAPH',
            'jumlah_halaman' => 60,
            'level_penerbit' => 'NATIONAL',
        ];

        $this->post(route('app.penghargaan.buku.store'), $payload)->assertRedirect();

        $this->assertDatabaseHas('book_authors', ['name' => 'Penulis A']);
        $this->assertDatabaseHas('book_authors', ['name' => 'Dosen Pengaju (Anda)']);
    }

    /** * Menutup Baris 291..319: Submit Validation Branches
     */
    #[Test]
    public function test_submit_validation_logic()
    {
        // Kasus 1: Bukan Draft (Baris 291-299)
        $bookA = $this->createFullBook(['status' => 'SUBMITTED']);
        $this->post(route('app.penghargaan.buku.submit', $bookA->id))
            ->assertSessionHas('error', 'Pengajuan sudah dikirim atau diproses.');

        // Kasus 2: Link kurang dari 5 (Baris 314-319)
        $bookB = $this->createFullBook(['drive_link' => json_encode(['l1', '', '', '', ''])]);
        $this->post(route('app.penghargaan.buku.submit', $bookB->id))
            ->assertSessionHas('error', 'Dokumen belum lengkap. Harap lengkapi semua link dokumen sebelum mengirim.');
    }

    /** * Menutup Baris 430..487: PDF Folder Creation & Regeneration
     */
    #[Test]
    public function test_pdf_folder_creation_and_regeneration()
    {
        // Hapus directory agar baris Storage::makeDirectory tereksekusi (Baris 470)
        Storage::disk('local')->deleteDirectory('public/pdfs/book-submissions');

        $book = $this->createFullBook(['pdf_path' => null]);

        // Panggil previewPdf untuk memicu generateAndSavePdf
        $response = $this->get(route('app.penghargaan.buku.preview-pdf', $book->id));

        $response->assertStatus(200);
        $this->assertNotNull($book->fresh()->pdf_path);
        Storage::disk('local')->assertExists('public/'.$book->fresh()->pdf_path);
    }

    /** * Menutup Baris 511..527: Download Errors & Label Mapping
     */
    #[Test]
    public function test_download_errors_and_mapping()
    {
        // 1. Download Fail (Baris 511-515)
        $book = $this->createFullBook(['pdf_path' => 'file_palsu.pdf']);
        $this->get(route('app.penghargaan.buku.download-pdf', $book->id))
            ->assertSessionHas('error', 'File PDF tidak ditemukan di server.');

        // 2. Mapping Match (Baris 524-525)
        BookSubmission::query()->delete();
        $this->createFullBook(['status' => 'VERIFIED_STAFF', 'book_type' => 'CHAPTER']);

        $response = $this->get(route('app.penghargaan.buku.index'));
        $response->assertInertia(fn (Inertia $page) => $page
            ->has('buku.0', fn ($page) => $page
                ->where('status', 'Menunggu Review Ketua')
                ->where('kategori', 'Book Chapter')
                ->etc()
            )
        );
    }

    /** * Menutup Baris 179-185: Catch block pada store
     */
    #[Test]
    public function test_store_catch_block_simulation()
    {
        // Simulasikan error DB saat commit
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollback')->once();
        DB::shouldReceive('commit')->andThrow(new \Exception('Simulated Error'));

        $payload = [
            'judul' => 'Buku Error',
            'penulis' => 'A',
            'penerbit' => 'P',
            'tahun' => 2025,
            'isbn' => 'I',
            'kategori' => 'REFERENCE',
            'jumlah_halaman' => 50,
            'level_penerbit' => 'NATIONAL',
        ];

        $response = $this->post(route('app.penghargaan.buku.store'), $payload);
        $response->assertSessionHasErrors(['error']);
    }

    #[Test]
    public function test_full_pdf_generation_flow()
    {
        // Pastikan directory belum ada untuk memicu baris 470 (makeDirectory)
        Storage::disk('local')->deleteDirectory('public/pdfs/book-submissions');

        $book = $this->createFullBook();

        // Panggil previewPdf. Karena pdf_path null, sistem akan menjalankan
        // generateAndSavePdf() sepenuhnya (Baris 453-505)
        $response = $this->get(route('app.penghargaan.buku.preview-pdf', $book->id));

        $response->assertStatus(200);
        $this->assertNotNull($book->fresh()->pdf_path);
        Storage::disk('local')->assertExists('public/'.$book->fresh()->pdf_path);
    }

    /** 2. Menutup Baris 55..56 & 524..527 (Mapping Status & Labels) */
    #[Test]
    public function test_index_mapping_with_diverse_statuses()
    {
        BookSubmission::query()->delete();

        // Buat data dengan status-status yang masuk ke match (PAID, REJECTED, dll)
        $this->createFullBook(['status' => 'PAID', 'book_type' => 'CHAPTER']);
        $this->createFullBook(['status' => 'REJECTED', 'book_type' => 'MONOGRAPH']);

        $response = $this->get(route('app.penghargaan.buku.index'));

        $response->assertInertia(fn (Inertia $page) => $page
            ->has('buku', 2)
            ->has('buku.0', fn ($p) => $p->where('status', 'Selesai (Cair)')->where('kategori', 'Book Chapter')->etc())
            ->has('buku.1', fn ($p) => $p->where('status', 'Ditolak/Perlu Revisi')->where('kategori', 'Monograf')->etc())
        );
    }

    /** 3. Menutup Baris 135..178 (Store, Transaksi, & Author Loop) */
    #[Test]
    public function test_store_transaction_and_author_loop()
    {
        $payload = [
            'judul' => 'Buku Baru',
            'penulis' => 'Andi, , Budi', // Spasi kosong untuk memicu !empty(cleanName)
            'penerbit' => 'Del',
            'tahun' => 2025,
            'isbn' => '999',
            'kategori' => 'TEACHING',
            'jumlah_halaman' => 45,
            'level_penerbit' => 'NATIONAL',
        ];

        $this->post(route('app.penghargaan.buku.store'), $payload)->assertRedirect();

        // Cek log (Baris 175)
        $this->assertDatabaseHas('submission_logs', ['action' => 'CREATE_DRAFT']);
        $this->assertDatabaseCount('book_authors', 3); // 1 First + 2 Member
    }

    /** 4. Menutup Baris 291..319 (Submit Logic) */
    #[Test]
    public function test_submit_logic_failure_branches()
    {
        // Link Kurang dari 5 (Baris 314-319)
        $book = $this->createFullBook(['drive_link' => json_encode(['l1', '', '', '', ''])]);

        $this->post(route('app.penghargaan.buku.submit', $book->id))
            ->assertSessionHas('error');
    }

    /** 5. Menutup Baris 511..515 (Download PDF) */
    #[Test]
    public function test_download_pdf_not_found_on_server()
    {
        $book = $this->createFullBook(['pdf_path' => 'file_palsu.pdf']);
        // JANGAN buat file fisiknya

        $this->get(route('app.penghargaan.buku.download-pdf', $book->id))
            ->assertSessionHas('error', 'File PDF tidak ditemukan di server.');
    }

    #[Test]
    public function test_trigger_pdf_directory_creation_and_generation()
    {
        // 1. Pastikan folder benar-benar tidak ada (Picu Baris 470)
        Storage::disk('local')->deleteDirectory('public/pdfs/book-submissions');

        // 2. Buat buku dengan pdf_path NULL (Picu Baris 430-505)
        $book = BookSubmission::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'title' => 'Buku Pemicu Coverage',
            'isbn' => '123-456',
            'publication_year' => 2025,
            'publisher' => 'Del Press',
            'publisher_level' => 'NATIONAL',
            'book_type' => 'REFERENCE',
            'total_pages' => 100,
            'status' => 'DRAFT',
            'drive_link' => json_encode(['l1', 'l2', 'l3', 'l4', 'l5']),
            'pdf_path' => null, // Memaksa regenerasi
        ]);

        // 3. Akses Preview
        $response = $this->get(route('app.penghargaan.buku.preview-pdf', $book->id));

        $response->assertStatus(200);
        Storage::disk('local')->assertExists('public/'.$book->fresh()->pdf_path);
    }

    /**
     * TARGET: Baris 524..527 (formatStatus & mapBookTypeToLabel)
     * Strategi: Gunakan status VERIFIED_STAFF dan kategori MONOGRAPH
     */
    #[Test]
    public function test_mapping_labels_comprehensively()
    {
        // Bersihkan DB agar data pertama (index 0) adalah data ini
        BookSubmission::query()->delete();

        BookSubmission::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'title' => 'Buku Label Test',
            'status' => 'VERIFIED_STAFF', // Picu 'Menunggu Review Ketua'
            'book_type' => 'MONOGRAPH',   // Picu 'Monograf'
            'publication_year' => 2025,
            'publisher' => 'IT Del',
            'publisher_level' => 'NATIONAL',
            'isbn' => '999',
            'total_pages' => 50,
        ]);

        $response = $this->get(route('app.penghargaan.buku.index'));

        $response->assertInertia(fn (Inertia $page) => $page
            ->has('buku.0', fn ($p) => $p
                ->where('status', 'Menunggu Review Ketua')
                ->where('kategori', 'Monograf')
                ->etc()
            )
        );
    }

    /**
     * TARGET: Baris 135..178 (Author loop whitespace)
     */
    #[Test]
    public function test_store_author_loop_with_empty_values()
    {
        $payload = [
            'judul' => 'Tes Penulis',
            'penulis' => 'Penulis 1, , Penulis 2', // Spasi kosong picu !empty(cleanName)
            'penerbit' => 'Del',
            'tahun' => 2025,
            'isbn' => '111',
            'kategori' => 'REFERENCE',
            'jumlah_halaman' => 100,
            'level_penerbit' => 'NATIONAL',
        ];

        $this->post(route('app.penghargaan.buku.store'), $payload);

        $this->assertDatabaseHas('book_authors', ['name' => 'Penulis 1']);
        $this->assertDatabaseHas('book_authors', ['name' => 'Penulis 2']);
    }

    #[Test]
    public function test_show_book_detail_with_profile_mapping()
    {
        $book = $this->createFullBook();
        BookAuthor::create([
            'book_submission_id' => $book->id,
            'name' => 'Penulis Pendamping',
            'role' => 'MEMBER',
        ]);

        $this->get(route('app.penghargaan.buku.detail', $book->id))
            ->assertStatus(200)
            ->assertInertia(fn (Inertia $page) => $page
                ->has('book.authors', 1)
                ->where('user.name', 'Dosen Pengaju')
            );
    }

    /** * 2. TEST CATCH BLOCK EXCEPTION (Menutup Baris yang Anda tanyakan: catch (\Exception $e))
     */
    #[Test]
    public function test_submit_triggers_catch_block_on_error()
    {
        $book = $this->createFullBook(['status' => 'DRAFT']);

        // 🔥 Trik: Paksa DB Error saat proses update untuk memicu blok catch
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollback')->once();
        DB::shouldReceive('commit')->andThrow(new \Exception('Simulated Database Error'));

        $response = $this->post(route('app.penghargaan.buku.submit', $book->id));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Gagal mengirim pengajuan', session('error'));
    }

    /** * 3. TEST PDF GENERATION & DIRECTORY (Menutup Baris 470 & 430..487)
     */
    #[Test]
    public function test_pdf_generation_full_cycle()
    {
        // Pastikan folder tidak ada (Picu baris 470)
        Storage::disk('local')->deleteDirectory('public/pdfs/book-submissions');

        $book = $this->createFullBook(['pdf_path' => null]);

        $this->get(route('app.penghargaan.buku.preview-pdf', $book->id))
            ->assertStatus(200);

        $this->assertNotNull($book->fresh()->pdf_path);
    }

    /** * 4. TEST MAPPING LABELS (Menutup Baris 55..56 & 524..525)
     */
    #[Test]
    public function test_format_status_and_type_labels()
    {
        BookSubmission::query()->delete();
        $this->createFullBook(['status' => 'REJECTED', 'book_type' => 'TEACHING']);

        $response = $this->get(route('app.penghargaan.buku.index'));

        $response->assertInertia(fn (Inertia $page) => $page
            ->has('buku.0', fn ($p) => $p
                ->where('status', 'Ditolak/Perlu Revisi')
                ->where('kategori', 'Buku Ajar')
                ->etc()
            )
        );
    }

    #[Test]
    public function it_covers_create_method_at_line_55_56()
    {
        // 1. Setup User
        $user = \App\Models\User::factory()->create();

        // 2. Eksekusi menggunakan Action Controller
        // Laravel akan otomatis mencari URL yang terhubung dengan method 'create'
        $response = $this->actingAs($user)->get(
            action([\App\Http\Controllers\App\Penghargaan\PenghargaanBukuController::class, 'create'])
        );

        // 3. Assert status sukses
        $response->assertStatus(200);

        // 4. Verifikasi Inertia (Menghijaukan baris 55-56)
        $response->assertInertia(fn ($page) => $page
            ->component('app/penghargaan/buku/create')
            ->where('pageName', 'Formulir Pengajuan Buku')
        );
    }

    #[Test]
    public function it_covers_upload_docs_method_at_line_173_to_178()
    {
        $user = \App\Models\User::factory()->create();
        $book = \App\Models\BookSubmission::factory()->create();

        // Panggil action dengan parameter ID
        $response = $this->actingAs($user)->get(
            action([\App\Http\Controllers\App\Penghargaan\PenghargaanBukuController::class, 'uploadDocs'], ['id' => $book->id])
        );

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('app/penghargaan/buku/upload-docs')
            ->where('bookId', $book->id)
            ->where('bookTitle', $book->title)
        );
    }

    #[Test]
    public function it_covers_store_upload_catch_block_at_line_224_to_232()
    {
        // 1. Setup Data
        $user = \App\Models\User::factory()->create();
        $book = \App\Models\BookSubmission::factory()->create();

        // Gunakan Log::spy() untuk memantau pemanggilan log
        $logSpy = \Illuminate\Support\Facades\Log::spy();

        // 2. SABOTASE DB: Paksa error agar masuk ke blok catch
        \Illuminate\Support\Facades\DB::shouldReceive('beginTransaction')->andReturn(null);
        \Illuminate\Support\Facades\DB::shouldReceive('rollback')->atLeast()->once();
        // Sabotase method kueri agar melempar Exception
        \Illuminate\Support\Facades\DB::shouldReceive('table')->andThrow(new \Exception('Database Error For Coverage'));

        // 3. Data Request yang sudah valid (links berupa array)
        $requestData = [
            'links' => ['https://example.com/document'],
            'file_pendukung' => \Illuminate\Http\UploadedFile::fake()->create('doc.pdf', 500),
        ];

        // 4. Eksekusi
        $response = $this->actingAs($user)->post(
            action([\App\Http\Controllers\App\Penghargaan\PenghargaanBukuController::class, 'storeUpload'], ['id' => $book->id]),
            $requestData
        );

        // 5. Assert Redirect & Session (Menghijaukan Baris 232)
        $response->assertStatus(302);
        $response->assertSessionHas('error');

        // 6. Assert Log (Menghijaukan Baris 226-230)
        // Kita gunakan atLeast()->once() karena sistem mungkin mencatat log tambahan
        $logSpy->shouldHaveReceived('error')->atLeast()->once();

        \Mockery::close();
    }

    #[Test]
    public function it_covers_preview_pdf_serving_stored_file_at_line_314_to_319()
    {
        // 1. Setup User & Data Buku
        $user = \App\Models\User::factory()->create();
        $pdfName = 'test_manual.pdf';
        $book = \App\Models\BookSubmission::factory()->create([
            'pdf_path' => $pdfName,
        ]);

        // 2. MOCK STORAGE: Pastikan Storage::exists return true
        \Illuminate\Support\Facades\Storage::shouldReceive('exists')
            ->with('public/'.$pdfName)
            ->andReturn(true);

        // 3. PHYSICAL FILE: Buat file fisik di lokasi yang dicari storage_path('app/public/...')
        // Di Windows, ini biasanya mengarah ke storage\app\public\test_manual.pdf
        $targetDir = storage_path('app/public');
        if (! file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $fullPath = $targetDir.DIRECTORY_SEPARATOR.$pdfName;
        file_put_contents($fullPath, 'fake pdf content');

        // 4. Spy Log
        $logSpy = \Illuminate\Support\Facades\Log::spy();

        // 5. Eksekusi
        try {
            $response = $this->actingAs($user)->get(
                action([\App\Http\Controllers\App\Penghargaan\PenghargaanBukuController::class, 'previewPdf'], ['id' => $book->id])
            );

            // 6. Assertions
            $response->assertStatus(200);

            // Verifikasi Log (Baris 316)
            $logSpy->shouldHaveReceived('info')->with(
                'Serving stored PDF',
                \Mockery::on(fn ($data) => str_contains($data['full_path'], $pdfName))
            )->once();

            // Jika masih gagal di assertInstanceOf, kita tampilkan response content untuk debug
            if (! ($response->baseResponse instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse)) {
                $this->fail('Gagal masuk ke BinaryFileResponse. Response content: '.$response->getContent());
            }

            $this->assertInstanceOf(
                \Symfony\Component\HttpFoundation\BinaryFileResponse::class,
                $response->baseResponse
            );

        } finally {
            // 7. Cleanup file fisik agar tidak mengotori storage asli
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            \Mockery::close();
        }
    }

    #[Test]
    public function it_covers_preview_pdf_catch_block_at_line_333_to_337()
    {
        // 1. Setup User & Data Buku
        $user = \App\Models\User::factory()->create();
        $book = \App\Models\BookSubmission::factory()->create([
            'pdf_path' => 'non_existent_file.pdf', // Pastikan file tidak ada agar masuk ke logika regenerasi
        ]);

        // 2. Setup Spy Log untuk memverifikasi baris 334
        $logSpy = \Illuminate\Support\Facades\Log::spy();

        // 3. SABOTASE: Paksa error saat sistem mencoba mengakses data untuk regenerasi
        // Kita mock model BookSubmission agar melempar exception saat memanggil kueri tertentu
        // atau jika ada pemanggilan PDF::loadView, kita sabotase di sana.

        // Cara termudah: Sabotase DB saat mencoba update/select di dalam blok try
        \Illuminate\Support\Facades\DB::shouldReceive('table')->andThrow(new \Exception('PDF Generation Failed'));

        // 4. Eksekusi
        $response = $this->actingAs($user)->get(
            action([\App\Http\Controllers\App\Penghargaan\PenghargaanBukuController::class, 'previewPdf'], ['id' => $book->id])
        );

        // 5. Assert: Pastikan masuk ke blok catch
        // Biasanya ini akan mengembalikan redirect back atau error 500 tergantung sisa kode Anda
        $response->assertStatus(500); // Atau 302 jika Anda menggunakan return back()

        // 6. Assert Log: Verifikasi Baris 334-337
        $logSpy->shouldHaveReceived('error')->with(
            'Failed to regenerate PDF',
            \Mockery::on(fn ($data) => $data['book_id'] === $book->id && isset($data['error']))
        )->once();

        \Mockery::close();
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
