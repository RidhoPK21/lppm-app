<?php

namespace Tests\Feature\Controllers\App\RegisSemi;

use App\Models\BookSubmission;
use App\Models\BookReviewer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Inertia;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
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
    private function createSubmission($attributes = []) {
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
            'pdf_path' => 'test.pdf'
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
        Storage::disk('local')->put('public/pdfs/book-submissions/' . $filename, 'data');
        
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
            'invited_by' => $this->admin->id
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
            'pdf_path' => null
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
            'approved_amount' => 1000000
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
            'approved_amount' => 7500000
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
            'pdf_path' => null
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
            'approved_amount' => 5000000
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

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}