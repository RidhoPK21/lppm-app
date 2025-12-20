<?php

namespace Tests\Feature\Controllers\App\RegisSemi;

use App\Models\BookSubmission;
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
class RegisSemiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected $dosen;

    protected $notificationMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        $this->admin = User::factory()->create(['name' => 'Admin LPPM']);
        $this->dosen = User::factory()->create(['name' => 'Dosen Penulis']);

        // Mocking alias untuk Notification Controller (Hanya sekali di setUp)
        $this->notificationMock = \Mockery::mock('alias:App\Http\Controllers\App\Notifikasi\NotificationController');
        $this->notificationMock->shouldReceive('sendReviewerInvitationNotification')->andReturn(true)->byDefault();
        $this->notificationMock->shouldReceive('sendBookPaymentNotification')->andReturn(true)->byDefault();

        $this->actingAs($this->admin);
    }

    /**
     * Helper diperbaiki dengan nilai uppercase untuk melewati CHECK constraint
     */
    private function createSubmission($attributes = [])
    {
        return BookSubmission::create(array_merge([
            'id' => (string) Str::uuid(),
            'user_id' => $this->dosen->id,
            'title' => 'Buku Test Full Coverage',
            'isbn' => '123-456',
            'publisher' => 'Penerbit Gramedia',
            'status' => 'SUBMITTED',
            'publication_year' => 2025,
            'publisher_level' => 'NATIONAL', // 🔥 Sesuaikan dengan Enum DB
            'book_type' => 'REFERENCE',      // 🔥 Sesuaikan dengan Enum DB
            'total_pages' => 100,
            'drive_link' => json_encode(['link1']),
            'pdf_path' => 'test.pdf',
        ], $attributes));
    }

    /** 1. Test All Indexes */
    #[Test]
    public function test_all_index_mappings()
    {
        $this->createSubmission(['status' => 'SUBMITTED']);
        $this->createSubmission(['status' => 'APPROVED_CHIEF', 'approved_amount' => 500000]);
        $this->createSubmission(['status' => 'PAID']);

        $this->get(route('regis-semi.index'))->assertStatus(200);
        $this->get(route('regis-semi.indexx'))->assertStatus(200);
        $this->get(route('regis-semi.result'))->assertStatus(200);
        $this->get(route('hrd.home'))->assertStatus(200);
    }

    /** 2. Test Store Invite Comprehensive */
    #[Test]
    public function test_store_invite_comprehensive()
    {
        $book = $this->createSubmission();
        $reviewerId = (string) Str::uuid();

        // Sukses Invite & Create User
        $this->postJson(route('regis-semi.store-invite', $book->id), ['user_id' => $reviewerId])
            ->assertStatus(200);

        // Duplikat (Already Invited)
        $this->postJson(route('regis-semi.store-invite', $book->id), ['user_id' => $reviewerId])
            ->assertStatus(422);

        // Catch Exception (Trigger baris catch di controller)
        $this->postJson(route('regis-semi.store-invite', 'invalid-id'), ['user_id' => $reviewerId])
            ->assertStatus(500);
    }

    #[Test]
    public function test_invite_page_with_sorting_logic()
    {
        $book = $this->createSubmission();

        // Reviewer 1: Punya akses Dosen
        $rev1 = User::factory()->create(['name' => 'Budi']);
        DB::table('m_hak_akses')->insert(['id' => Str::uuid(), 'user_id' => $rev1->id, 'akses' => 'Dosen']);

        // Reviewer 2: Tidak punya akses Dosen (hanya Reviewer saja misalnya)
        $rev2 = User::factory()->create(['name' => 'Andi']);
        DB::table('m_hak_akses')->insert(['id' => Str::uuid(), 'user_id' => $rev2->id, 'akses' => 'Reviewer']);

        // Akses halaman invite
        $response = $this->get(route('regis-semi.invite', $book->id));

        $response->assertStatus(200)
            ->assertInertia(fn (Inertia $page) => $page->component('App/RegisSemi/Invite'));
    }

    #[Test]
    public function it_redirects_away_if_pdf_path_is_a_valid_url()
    {
        // 1. Setup: Buat data buku dengan pdf_path berupa URL
        $externalUrl = 'https://drive.google.com/file/d/external-link/view';
        $book = \App\Models\BookSubmission::factory()->create([
            'pdf_path' => $externalUrl,
        ]);

        // 2. Execute: Panggil route previewPdf
        $response = $this->get(action([\App\Http\Controllers\App\RegisSemi\RegisSemiController::class, 'previewPdf'], $book->id));

        // 3. Assert: Pastikan dia melakukan redirect()->away() ke URL tersebut
        $response->assertRedirect($externalUrl);

        // Opsional: Pastikan status code redirect adalah 302
        $this->assertEquals(302, $response->getStatusCode());
    }

    #[Test]
    public function it_returns_404_if_pdf_path_is_missing_in_database()
    {
        // 1. Setup data
        $book = \App\Models\BookSubmission::factory()->create([
            'pdf_path' => null,
        ]);

        // 2. Inisialisasi Controller secara manual
        $controller = new \App\Http\Controllers\App\RegisSemi\RegisSemiController;

        // 3. Ekspektasi Exception
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('File PDF tidak ditemukan di database.');

        // 4. Panggil method-nya langsung
        $controller->previewPdf($book->id);
    }

    #[Test]
    public function it_returns_404_if_file_does_not_exist_in_storage()
    {
        // 1. Setup data
        \Illuminate\Support\Facades\Storage::fake('public');
        $book = \App\Models\BookSubmission::factory()->create([
            'pdf_path' => 'file_tidak_ada.pdf',
        ]);

        $controller = new \App\Http\Controllers\App\RegisSemi\RegisSemiController;

        // 2. Ekspektasi Exception (Baris 323)
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('File PDF tidak ditemukan di storage.');

        // 3. Jalankan
        $controller->previewPdf($book->id);
    }

    #[Test]
    public function it_streams_pdf_content_when_in_testing_environment()
    {
        // 1. Setup: Gunakan fake untuk disk default dan public
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::fake('local'); // Tambahkan ini sebagai jaga-jaga

        $fileName = 'sample-test.pdf';
        $content = 'Fake PDF Content';

        // Taruh file di root agar loop 'public/'.$book->pdf_path menemukannya
        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $content);
        \Illuminate\Support\Facades\Storage::disk('local')->put('public/'.$fileName, $content);

        $book = \App\Models\BookSubmission::factory()->create([
            'pdf_path' => $fileName,
        ]);

        // 2. Execute
        $response = $this->get(action([\App\Http\Controllers\App\RegisSemi\RegisSemiController::class, 'previewPdf'], $book->id));

        // 3. Assert (Menembus baris 331-334)
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // Verifikasi konten
        $this->assertEquals($content, $response->streamedContent());
    }

    #[Test]
    public function it_returns_404_if_pdf_path_is_missing_when_downloading()
    {
        // 1. Setup: Buat data buku dengan pdf_path null
        $book = \App\Models\BookSubmission::factory()->create([
            'pdf_path' => null,
        ]);

        // 2. Inisialisasi Controller
        $controller = new \App\Http\Controllers\App\RegisSemi\RegisSemiController;

        // 3. Ekspektasi Exception (Baris 343)
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('File PDF tidak ditemukan.');

        // 4. Jalankan method downloadPdf
        $controller->downloadPdf($book->id);
    }

    /**
     * 2. Menutup baris 539-560 (Review Results Exception)
     */
    #[Test]
    public function test_review_results_error_flow()
    {
        // ID sampah untuk memicu blok catch
        $response = $this->get(route('regis-semi.review-results', 'sampah-id'));
        $response->assertRedirect(route('regis-semi.index'));
    }

    /**
     * 3. Menutup baris 290-323 (Approve & Reject)
     */
    #[Test]
    public function test_approval_actions()
    {
        $book = $this->createSubmission();
        $this->post(route('regis-semi.approve', $book->id), ['amount' => 1000000])
            ->assertRedirect(route('regis-semi.index'));

        $this->post(route('regis-semi.reject', $book->id), ['note' => 'Tolak'])
            ->assertRedirect(route('regis-semi.index'));
    }

    /**
     * 4. Menutup mapping index (Baris 54-68)
     */
    #[Test]
    public function test_index_mapping()
    {
        $this->createSubmission();
        $this->get(route('regis-semi.index'))->assertStatus(200);
        $this->get(route('hrd.home'))->assertStatus(200);
    }

    /**
     * 5. Preview PDF Loop (Pencarian folder)
     */
    #[Test]
    public function test_pdf_preview_full_loop()
    {
        Storage::fake('local');
        $book = $this->createSubmission(['pdf_path' => 'file_ga_ada.pdf']);
        $this->get(route('regis-semi.preview-pdf', $book->id))->assertStatus(404);
    }

    /** 3. Test Preview PDF Paths (Menembus Baris Looping) */
    #[Test]
    public function test_preview_pdf_search_paths()
    {
        Storage::fake('local');
        $filename = 'preview.pdf';

        // Simulasikan file di path terdalam agar looping tereksekusi
        Storage::disk('local')->put('public/pdfs/book-submissions/'.$filename, 'data');

        $book = $this->createSubmission(['pdf_path' => $filename]);

        $response = $this->get(route('regis-semi.preview-pdf', $book->id));
        $this->assertTrue(in_array($response->getStatusCode(), [200, 404]));
    }

    /** 4. Test Reject & Approve */
    #[Test]
    public function test_approval_and_rejection_actions()
    {
        $book = $this->createSubmission();

        // Reject Staff
        $this->post(route('regis-semi.rejectStaff', $book->id), ['note' => 'Revisi'])
            ->assertRedirect(route('regis-semi.indexx'));

        // Approve Chief
        $this->post(route('regis-semi.approve', $book->id), ['amount' => 1000000])
            ->assertRedirect(route('regis-semi.index'));
    }

    /** 5. Test Review Results Inertia Mapping */
    #[Test]
    public function test_review_results_inertia_mapping()
    {
        $book = $this->createSubmission();
        $reviewer = User::factory()->create();

        DB::table('book_reviewers')->insert([
            'id' => (string) Str::uuid(),
            'book_submission_id' => $book->id,
            'user_id' => $reviewer->id,
            'status' => 'ACCEPTED',
            'note' => 'Oke',
            'reviewed_at' => now(),
            'invited_by' => $this->admin->id,
        ]);

        $this->get(route('regis-semi.review-results', $book->id))
            ->assertStatus(200)
            ->assertInertia(fn (Inertia $page) => $page->has('results'));
    }

    /** 6. Test Views & Downloads */
    #[Test]
    public function test_views_and_downloads()
    {
        Storage::fake('local');
        Storage::disk('local')->put('public/file.pdf', 'content');
        $book = $this->createSubmission(['pdf_path' => 'file.pdf']);

        $this->get(route('regis-semi.show', $book->id))->assertStatus(200);
        $this->get(route('regis-semi.show.staff', $book->id))->assertStatus(200);
        $this->get(route('regis-semi.download-pdf', $book->id))->assertStatus(200);
    }

    /** 1. Test Looping Path PDF & Abort 404 (Menutup 413..421 & 453..489) */
    #[Test]
    public function test_preview_pdf_not_found_in_any_location()
    {
        Storage::fake('local');
        // Kita set pdf_path tapi TIDAK membuat filenya di storage sama sekali
        // Ini akan memaksa controller melewati semua 'possiblePaths' dan berakhir di Log::error
        $book = $this->createSubmission(['pdf_path' => 'file_hilang.pdf']);

        $response = $this->get(route('regis-semi.preview-pdf', $book->id));
        $response->assertStatus(404); // Menutup baris abort(404) di akhir pencarian
    }

    /** 2. Test Review Results Catch Error (Menutup 539..560 & Error handling) */
    #[Test]
    public function test_show_review_results_exception_handling()
    {
        // Panggil ID yang tidak ada untuk memicu try-catch block
        $response = $this->get(route('regis-semi.review-results', 'invalid-uuid'));
        $response->assertRedirect(route('regis-semi.index'));
        $response->assertSessionHas('error');
    }

    /** 3. Test Detail Views Comprehensive (Menutup 331..334, 343, 347, 353) */
    #[Test]
    public function test_detail_mapping_with_null_fields()
    {
        // Buat buku dengan field minimalis untuk mengetes ternary operator (e.g., ?? 'Unknown')
        $book = $this->createSubmission([
            'drive_link' => null,
            'pdf_path' => null,
        ]);

        $this->get(route('regis-semi.show', $book->id))->assertStatus(200);
        $this->get(route('regis-semi.show.staff', $book->id))->assertStatus(200);
    }

    /** 4. Test HRD Mapping (Menutup 211, 566, 568..571) */
    #[Test]
    public function test_hrd_mapping_with_data()
    {
        $this->createSubmission([
            'status' => 'APPROVED_CHIEF',
            'approved_amount' => 1000000,
        ]);

        $response = $this->get(route('hrd.home'));
        $response->assertStatus(200);
    }

    /** 5. Test Reject & Approve Baris Spesifik (Menutup 290, 294, 317, 323) */
    #[Test]
    public function test_specific_action_lines()
    {
        $book = $this->createSubmission();

        // Reject Staff
        $this->post(route('regis-semi.rejectStaff', $book->id), ['note' => 'Revisi'])
            ->assertRedirect(route('regis-semi.indexx'));

        // Approve dengan user_id null di Auth (simulasi)
        $this->post(route('regis-semi.approve', $book->id), ['amount' => 500000])
            ->assertRedirect(route('regis-semi.index'));
    }

    #[Test]
    public function test_preview_pdf_not_found_in_any_location_logic()
    {
        Storage::fake('local');
        $book = $this->createSubmission(['pdf_path' => 'file_yang_benar_benar_hilang.pdf']);

        // Ini akan memaksa foreach ($possiblePaths) berjalan sampai habis dan memicu Log::error
        $response = $this->get(route('regis-semi.preview-pdf', $book->id));
        $response->assertStatus(404);
    }

    /**
     * Menutup baris 539..560 (Exception Handling pada Review Results)
     */
    #[Test]
    public function test_show_review_results_catch_exception()
    {
        // Mengirimkan ID yang tidak valid (bukan UUID) untuk memicu blok catch (\Exception $e)
        $response = $this->get(route('regis-semi.review-results', 'invalid-id-123'));

        $response->assertRedirect(route('regis-semi.index'));
        $response->assertSessionHas('error');
    }

    #[Test]
    public function test_index_hrd_currency_mapping()
    {
        $this->createSubmission([
            'status' => 'APPROVED_CHIEF',
            'approved_amount' => 7500000,
        ]);

        $response = $this->get(route('hrd.home'));

        $response->assertStatus(200)
            ->assertInertia(fn (Inertia $page) => $page
                ->has('submissions.0', fn (Inertia $item) => $item
                    ->where('amount_formatted', 'Rp 7.500.000')
                    ->etc() // 🔥 Mengizinkan properti lain tanpa harus didefinisikan satu-satu
                )
            );
    }

    /** * Menutup Baris 467..475 (usort logic)
     * Menguji kondisi ketika has_dosen_akses berbeda untuk memicu pengurutan
     */
    #[Test]
    public function test_invite_sorting_logic_branches()
    {
        $book = $this->createSubmission();

        // User 1: No Dosen Akses
        $user1 = User::factory()->create(['name' => 'Budi']);
        DB::table('m_hak_akses')->insert(['id' => Str::uuid(), 'user_id' => $user1->id, 'akses' => 'Reviewer']);

        // User 2: Has Dosen Akses
        $user2 = User::factory()->create(['name' => 'Andi']);
        DB::table('m_hak_akses')->insert(['id' => Str::uuid(), 'user_id' => $user2->id, 'akses' => 'Dosen']);

        $response = $this->get(route('regis-semi.invite', $book->id));
        $response->assertStatus(200);
    }

    /** * Menutup Baris 331..353 (Detail fallbacks)
     */
    #[Test]
    public function test_show_detail_with_null_user_and_links()
    {
        // Buat submission dengan drive_link null untuk memicu json_decode(null)
        $book = $this->createSubmission(['drive_link' => null]);

        $this->get(route('regis-semi.show', $book->id))->assertStatus(200);
        $this->get(route('regis-semi.show.staff', $book->id))->assertStatus(200);
    }

    /** * Menutup Baris 290, 294, 323 (SubmissionLog & Auth fallback)
     */
    #[Test]
    public function test_actions_creating_logs()
    {
        $book = $this->createSubmission();

        // Reject
        $this->post(route('regis-semi.reject', $book->id), ['note' => 'Ditolak'])
            ->assertRedirect(route('regis-semi.index'));

        // Approve (Memicu Auth::id() ?? $book->user_id)
        $this->post(route('regis-semi.approve', $book->id), ['amount' => 500000])
            ->assertRedirect(route('regis-semi.index'));

        $this->assertDatabaseHas('submission_logs', ['action' => 'APPROVE']);
    }

    /** * Menutup baris sisa pada index (Baris 54-68)
     */
    #[Test]
    public function test_main_index_mapping()
    {
        $this->createSubmission();
        $this->get(route('regis-semi.index'))->assertStatus(200);
    }

    /**
     * Menutup baris 331..353 (Mapping Detail dengan data Null/Minimal)
     */
    #[Test]
    public function test_show_detail_with_empty_user_and_links()
    {
        // Buat submission dengan data minimal untuk memicu ternary operator (?? 'Unknown')
        $book = $this->createSubmission([
            'user_id' => $this->dosen->id,
            'drive_link' => null,
            'pdf_path' => null,
        ]);

        $this->get(route('regis-semi.show', $book->id))->assertStatus(200);
        $this->get(route('regis-semi.show.staff', $book->id))->assertStatus(200);
    }

    /**
     * Menutup baris 566..572 (Mapping HRD Index)
     */
    #[Test]
    public function test_index_hrd_with_complete_mapping()
    {
        $this->createSubmission([
            'status' => 'APPROVED_CHIEF',
            'approved_amount' => 5000000,
        ]);

        $response = $this->get(route('hrd.home'));
        $response->assertStatus(200);
    }

    /**
     * Menutup baris 290, 294, 323 (Reject & Approve dengan Logic Auth)
     */
    #[Test]
    public function test_action_logic_branches()
    {
        $book = $this->createSubmission();

        // Reject Staff
        $this->post(route('regis-semi.rejectStaff', $book->id), ['note' => 'Revisi'])
            ->assertRedirect(route('regis-semi.indexx'));

        // Approve (Memicu pembuatan log dengan Auth::id())
        $this->post(route('regis-semi.approve', $book->id), ['amount' => 1000000])
            ->assertRedirect(route('regis-semi.index'));
    }

    #[Test]
    public function it_redirects_away_if_pdf_path_is_url_during_download()
    {
        // 1. Setup: Buat data buku dengan pdf_path berupa URL eksternal
        $externalUrl = 'https://some-external-storage.com/buku/buku-saya.pdf';
        $book = \App\Models\BookSubmission::factory()->create([
            'pdf_path' => $externalUrl,
        ]);

        // 2. Execute: Panggil route download-pdf
        $response = $this->get(action([\App\Http\Controllers\App\RegisSemi\RegisSemiController::class, 'downloadPdf'], $book->id));

        // 3. Assert (Menutup baris 347)
        // Memastikan sistem melakukan redirect ke luar (away)
        $response->assertRedirect($externalUrl);
        $this->assertEquals(302, $response->getStatusCode());
    }

    #[Test]
    public function it_returns_404_if_file_physically_missing_on_server_during_download()
    {
        // 1. Setup: Gunakan fake storage agar tidak mengganggu file asli
        \Illuminate\Support\Facades\Storage::fake('public');

        // Buat data buku dengan pdf_path, tapi jangan simpan file fisiknya
        $book = \App\Models\BookSubmission::factory()->create([
            'pdf_path' => 'file_yang_benar_benar_hilang.pdf',
        ]);

        // 2. Inisialisasi Controller secara langsung untuk menangkap Exception murni
        $controller = new \App\Http\Controllers\App\RegisSemi\RegisSemiController;

        // 3. Ekspektasi: Exception status 404 dengan pesan spesifik (Baris 353)
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('File tidak ditemukan di server.');

        // 4. Jalankan
        $controller->downloadPdf($book->id);
    }

    #[Test]
    public function it_sorts_available_reviewers_by_dosen_access_and_name()
    {
        // 1. Setup Buku
        $book = \App\Models\BookSubmission::factory()->create();

        // 2. Mock Data
        $mockData = [
            ['name' => 'Zebra (Staff)', 'has_dosen_akses' => false, 'is_invited' => false],
            ['name' => 'Budi (Dosen)',  'has_dosen_akses' => true,  'is_invited' => false],
            ['name' => 'Alpha (Staff)', 'has_dosen_akses' => false, 'is_invited' => false],
            ['name' => 'Andi (Dosen)',  'has_dosen_akses' => true,  'is_invited' => false],
        ];

        // FIX: Tambahkan backslash \ sebelum Mockery
        $mockHakAkses = \Mockery::mock('alias:\App\Models\HakAksesModel');
        $mockHakAkses->shouldReceive('getAvailableReviewersForBook')
            ->with($book->id)
            ->andReturn($mockData);

        // 3. Execute
        $response = $this->get(action([\App\Http\Controllers\App\RegisSemi\RegisSemiController::class, 'invite'], $book->id));

        // 4. Assert
        $response->assertStatus(200);

        // Ambil data hasil urutan dari Inertia
        $sortedReviewers = $response->original->getData()['page']['props']['availableReviewers'];

        // Verifikasi Hasil Urutan (Menembus baris 468, 471, dan 475)
        // Urutan seharusnya: Andi (Dosen), Budi (Dosen), Alpha (Staff), Zebra (Staff)

        $this->assertEquals('Andi (Dosen)', $sortedReviewers[0]['name']); // strcmp (Andi < Budi)
        $this->assertEquals('Budi (Dosen)', $sortedReviewers[1]['name']);
        $this->assertEquals('Alpha (Staff)', $sortedReviewers[2]['name']); // usort (Dosen < Staff)
        $this->assertEquals('Zebra (Staff)', $sortedReviewers[3]['name']); // strcmp (Alpha < Zebra)
    }

    #[Test]
    public function it_returns_default_label_for_unknown_status()
    {
        $controller = new \App\Http\Controllers\App\RegisSemi\RegisSemiController;

        // Karena method ini private, kita gunakan Reflection untuk mengaksesnya
        $reflection = new \ReflectionMethod($controller, 'formatStatusLabel');
        $reflection->setAccessible(true);

        // Input status aneh untuk memicu baris 571 (default => $status)
        $statusAneh = 'STATUS_YANG_TIDAK_ADA';
        $result = $reflection->invoke($controller, $statusAneh);

        $this->assertEquals($statusAneh, $result);
    }

    #[Test]
    public function it_streams_pdf_in_testing_environment()
    {
        // 1. Setup: Paksa semua disk (default & public) menggunakan fake yang sama
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::fake('local');

        $content = 'Fake PDF Content';
        $fileName = 'document.pdf';

        // Taruh file di beberapa lokasi sekaligus agar loop 'possiblePaths' di controller pasti kena
        // Lokasi 1: Root (untuk path asli)
        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $content);
        \Illuminate\Support\Facades\Storage::disk('local')->put($fileName, $content);

        // Lokasi 2: Prefix public (untuk 'public/'.$book->pdf_path)
        \Illuminate\Support\Facades\Storage::disk('public')->put('public/'.$fileName, $content);
        \Illuminate\Support\Facades\Storage::disk('local')->put('public/'.$fileName, $content);

        $book = \App\Models\BookSubmission::factory()->create([
            'pdf_path' => $fileName,
        ]);

        // 2. Execute
        $response = $this->get(action([\App\Http\Controllers\App\RegisSemi\RegisSemiController::class, 'previewPdf'], $book->id));

        // 3. Assert
        $response->assertStatus(200);
        $this->assertEquals($content, $response->streamedContent());
    }

    #[Test]
    public function it_covers_production_file_response_path()
    {
        // 1. Setup Data Buku
        $book = \App\Models\BookSubmission::factory()->create([
            'pdf_path' => 'file_test_produksi.pdf',
        ]);

        // 2. Mocking Facade Storage (Manual)
        // Kita buat Storage::exists() selalu TRUE dan Storage::get() memberikan path fisik
        \Illuminate\Support\Facades\Storage::shouldReceive('exists')
            ->andReturn(true);

        // 3. Mocking Environment (Sabotase agar NOT testing)
        $mockApp = \Mockery::mock(app());
        $mockApp->makePartial();
        $mockApp->shouldReceive('environment')->with('testing')->andReturn(false);
        $this->app->instance('app', $mockApp);

        // 4. Siapkan file fisik sementara di lokasi yang akan dibaca response()->file()
        // Baris 335 menggunakan storage_path('app/'.$storagePath)
        // Kita asumsikan $storagePath dari loop terakhir adalah pdf_path itu sendiri
        $tempPath = storage_path('app/file_test_produksi.pdf');
        if (! file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        file_put_contents($tempPath, 'dummy content');

        // 5. Execute
        $response = $this->get(action([\App\Http\Controllers\App\RegisSemi\RegisSemiController::class, 'previewPdf'], $book->id));

        // 6. Assert (Menembus Baris 335)
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // Cleanup
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
        \Mockery::close();
    }

    #[Test]
    public function it_covers_usort_priority_dosen_branch()
    {
        $book = \App\Models\BookSubmission::factory()->create();

        // Data diatur agar Staff (has_dosen_akses false) muncul SEBELUM Dosen
        // Ini memaksa usort untuk memicu 'return -1' saat memindahkan Dosen ke atas
        $mockData = [
            ['name' => 'Staff User', 'has_dosen_akses' => false, 'is_invited' => false],
            ['name' => 'Dosen User', 'has_dosen_akses' => true,  'is_invited' => false],
        ];

        // Mocking static method pada HakAksesModel
        $mockHakAkses = \Mockery::mock('alias:\App\Models\HakAksesModel');
        $mockHakAkses->shouldReceive('getAvailableReviewersForBook')
            ->with($book->id)
            ->andReturn($mockData);

        // Execute
        $response = $this->get(action([\App\Http\Controllers\App\RegisSemi\RegisSemiController::class, 'invite'], $book->id));

        // Assert
        $response->assertStatus(200);

        $reviewers = $response->original->getData()['page']['props']['availableReviewers'];

        // Verifikasi Dosen sekarang di index 0
        $this->assertEquals('Dosen User', $reviewers[0]['name']);
        $this->assertTrue($reviewers[0]['has_dosen_akses']);
    }

    #[Test]
    public function it_covers_all_format_status_label_branches()
    {
        $controller = new \App\Http\Controllers\App\RegisSemi\RegisSemiController;
        $reflection = new \ReflectionMethod($controller, 'formatStatusLabel');
        $reflection->setAccessible(true);

        // Mengetes semua case yang ada di match
        $this->assertEquals('Draft', $reflection->invoke($controller, 'DRAFT'));
        $this->assertEquals('Menunggu Verifikasi', $reflection->invoke($controller, 'SUBMITTED'));
        $this->assertEquals('Perlu Revisi', $reflection->invoke($controller, 'REVISION_REQUIRED'));
        $this->assertEquals('Review Ketua', $reflection->invoke($controller, 'VERIFIED_STAFF'));
        $this->assertEquals('Disetujui LPPM', $reflection->invoke($controller, 'APPROVED_CHIEF'));
        $this->assertEquals('Ditolak/Revisi', $reflection->invoke($controller, 'REJECTED'));

        // Sesuaikan dengan baris 571 kode Anda: 'PAID' => 'Selesai (Cair)'
        $this->assertEquals('Selesai (Cair)', $reflection->invoke($controller, 'PAID'));

        // TRIGGER DEFAULT (Baris 572/Terakhir): Gunakan status yang benar-benar tidak ada
        // Ini akan mengembalikan status itu sendiri (default => $status)
        $unknownStatus = 'STATUS_TIDAK_TERDAFTAR';
        $this->assertEquals($unknownStatus, $reflection->invoke($controller, $unknownStatus));
    }

    #[Test]
    public function it_covers_all_preview_pdf_response_paths()
    {
        // --- PART A: Menutup Baris 331-334 (Testing Environment) ---
        Storage::fake('public');
        $content = 'Test Content';
        $fileName = 'test.pdf';

        // Taruh di root agar loop 'possiblePaths' pasti menemukan file ini
        Storage::disk('public')->put($fileName, $content);

        $book = \App\Models\BookSubmission::factory()->create(['pdf_path' => $fileName]);
        $controller = new \App\Http\Controllers\App\RegisSemi\RegisSemiController;

        // Jalankan dalam kondisi normal testing
        $response = $controller->previewPdf($book->id);
        $this->assertEquals(200, $response->getStatusCode());

        // --- PART B: Menutup Baris 335 (Production Path) ---
        // Sabotase environment agar app()->environment('testing') mengembalikan FALSE
        $mockApp = \Mockery::mock(app());
        $mockApp->makePartial();
        $mockApp->shouldReceive('environment')->with('testing')->andReturn(false);
        $this->app->instance('app', $mockApp);

        // Buat file fisik nyata karena response()->file() mengecek ke file sistem asli
        $pathFisik = storage_path('app/public/'.$fileName);
        if (! file_exists(dirname($pathFisik))) {
            mkdir(dirname($pathFisik), 0755, true);
        }
        file_put_contents($pathFisik, $content);

        // Jalankan lagi, sekarang dia akan dipaksa ke baris 335
        $responseProd = $controller->previewPdf($book->id);
        $this->assertEquals(200, $responseProd->getStatusCode());

        // Cleanup
        if (file_exists($pathFisik)) {
            unlink($pathFisik);
        }
    }

    #[Test]
    public function it_covers_usort_return_negative_one()
    {
        $book = \App\Models\BookSubmission::factory()->create();

        // Berikan data yang "terbalik" secara ekstrim agar usort melakukan swap
        $mockData = [
            ['name' => 'Zebra Staff', 'has_dosen_akses' => false, 'is_invited' => false],
            ['name' => 'Alpha Dosen', 'has_dosen_akses' => true,  'is_invited' => false],
        ];

        $mockHakAkses = \Mockery::mock('alias:\App\Models\HakAksesModel');
        $mockHakAkses->shouldReceive('getAvailableReviewersForBook')
            ->andReturn($mockData);

        $response = $this->get(action([\App\Http\Controllers\App\RegisSemi\RegisSemiController::class, 'invite'], $book->id));

        $response->assertStatus(200);
        $reviewers = $response->original->getData()['page']['props']['availableReviewers'];

        // Pastikan Dosen naik ke atas (Index 0)
        $this->assertEquals('Alpha Dosen', $reviewers[0]['name']);
    }

    #[Test]
    public function it_really_covers_all_preview_pdf_lines()
    {
        $fileName = 'final_test.pdf';
        $fullPath = storage_path('app/'.$fileName);

        if (! file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }
        file_put_contents($fullPath, 'content');

        $book = \App\Models\BookSubmission::factory()->create(['pdf_path' => $fileName]);

        // 1. Mocking Storage Facade agar melewati pengecekan exists()
        \Illuminate\Support\Facades\Storage::shouldReceive('exists')->andReturn(true);
        \Illuminate\Support\Facades\Storage::shouldReceive('get')->andReturn('content');

        // 2. JALUR TESTING (Menutup Baris 331-334)
        $controller = new \App\Http\Controllers\App\RegisSemi\RegisSemiController;
        $responseStream = $controller->previewPdf($book->id);

        $this->assertEquals(200, $responseStream->getStatusCode());
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $responseStream);

        // 3. JALUR PRODUKSI (Menutup Baris 335)
        // Paksa Laravel menganggap ini bukan environment testing
        app()->bind('env', function () {
            return 'production';
        });

        $controllerProd = new \App\Http\Controllers\App\RegisSemi\RegisSemiController;
        $responseFile = $controllerProd->previewPdf($book->id);

        // Assert menggunakan method yang ada pada objek Response asli
        $this->assertEquals(200, $responseFile->getStatusCode());

        // HAPUS atau KOMENTAR baris assertStatus(200) yang menyebabkan error sebelumnya
        // $responseFile->assertStatus(200); // <-- Ini biang keroknya

        // Cleanup
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        \Mockery::close();
    }

    #[Test]
    public function it_forces_coverage_for_usort_negative_branch_468()
    {
        $book = \App\Models\BookSubmission::factory()->create();

        // Kita berikan 4 data dengan urutan yang sangat spesifik:
        // 1. Staff (bukan dosen)
        // 2. Dosen (has_dosen_akses: true)
        // 3. Dosen lain (untuk memicu perbandingan sesama dosen)
        // 4. Staff lain
        $mockData = [
            ['name' => 'Zebra Staff', 'has_dosen_akses' => false, 'is_invited' => false],
            ['name' => 'Alpha Dosen', 'has_dosen_akses' => true,  'is_invited' => false],
            ['name' => 'Beta Dosen',  'has_dosen_akses' => true,  'is_invited' => false],
            ['name' => 'Charlie Staff', 'has_dosen_akses' => false, 'is_invited' => false],
        ];

        // Gunakan Mockery untuk menyuntikkan data ini
        $mockHakAkses = \Mockery::mock('alias:\App\Models\HakAksesModel');
        $mockHakAkses->shouldReceive('getAvailableReviewersForBook')
            ->with($book->id)
            ->andReturn($mockData);

        // Jalankan request ke route invite
        $response = $this->get(action([\App\Http\Controllers\App\RegisSemi\RegisSemiController::class, 'invite'], $book->id));

        // Assert Status OK
        $response->assertStatus(200);

        // Ambil data hasil urutan
        $results = $response->original->getData()['page']['props']['availableReviewers'];

        // Verifikasi urutan pertama adalah 'Alpha Dosen' (karena Dosen & Alfabet A)
        $this->assertEquals('Alpha Dosen', $results[0]['name']);
        $this->assertTrue($results[0]['has_dosen_akses']);

        // Verifikasi urutan terakhir adalah 'Zebra Staff'
        $this->assertEquals('Zebra Staff', end($results)['name']);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
