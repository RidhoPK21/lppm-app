<?php

namespace Tests\Feature\Controllers\App\HRD;

use App\Models\BookSubmission;
use App\Models\SubmissionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Inertia;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class HRDControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $hrdUser;
    protected $dosenUser;
    protected $notificationMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        // 1. Setup Users
        $this->hrdUser = User::factory()->create(['name' => 'Staf HRD']);
        $this->dosenUser = User::factory()->create(['name' => 'Dosen Pengaju']);

        // 2. Mocking Notification Controller Alias (Static Method)
        $this->notificationMock = \Mockery::mock('alias:App\Http\Controllers\App\Notifikasi\NotificationController');
        $this->notificationMock->shouldReceive('sendBookPaymentSuccessNotification')->andReturn(true)->byDefault();

        $this->actingAs($this->hrdUser);
    }

    /**
     * TEST: Menampilkan daftar buku yang siap dicairkan (APPROVED_CHIEF)
     */
    #[Test]
    public function test_index_displays_approved_books_only()
    {
        // Buku yang harus muncul
        BookSubmission::factory()->create([
            'user_id' => $this->dosenUser->id,
            'title' => 'Buku Siap Cair',
            'status' => 'APPROVED_CHIEF',
        ]);

        // Buku yang TIDAK boleh muncul
        BookSubmission::factory()->create([
            'user_id' => $this->dosenUser->id,
            'title' => 'Buku Sudah Dibayar',
            'status' => 'PAID',
        ]);

        $response = $this->get(route('hrd.kita.index'));

        $response->assertStatus(200)
            ->assertInertia(fn (Inertia $page) => $page
                ->component('app/home/kita-page')
                ->has('submissions', 1)
                ->where('submissions.0.judul', 'Buku Siap Cair')
                ->where('submissions.0.status_label', 'Disetujui LPPM')
            );
    }

    /**
     * TEST: Proses pencairan dana berhasil
     */
    #[Test]
    public function test_store_pencairan_success()
    {
        $book = BookSubmission::factory()->create([
            'user_id' => $this->dosenUser->id,
            'title' => 'Buku Untuk Dicairkan',
            'status' => 'APPROVED_CHIEF',
        ]);

        $payload = [
            'book_id' => $book->id,
            'payment_date' => now()->format('Y-m-d'),
        ];

        $response = $this->post(route('hrd.pencairan'), $payload);

        $response->assertRedirect(route('hrd.kita.index'))
            ->assertSessionHas('success', 'Pencairan dana berhasil diproses!');

        // Verifikasi perubahan data
        $book->refresh();
        $this->assertEquals('PAID', $book->status);
        $this->assertNotNull($book->payment_date);

        // Verifikasi log aktivitas
        $this->assertDatabaseHas('submission_logs', [
            'book_submission_id' => $book->id,
            'action' => 'PAYMENT_DISBURSED',
            'user_id' => $this->hrdUser->id
        ]);
    }

    /**
     * TEST: Pencairan gagal jika status bukan APPROVED_CHIEF
     */
    #[Test]
    public function test_store_pencairan_fails_if_status_invalid()
    {
        $book = BookSubmission::factory()->create([
            'status' => 'SUBMITTED', // Bukan APPROVED_CHIEF
        ]);

        $response = $this->post(route('hrd.pencairan'), [
            'book_id' => $book->id,
            'payment_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHas('error', 'Buku tidak dalam status yang valid untuk pencairan.');
        $this->assertEquals('SUBMITTED', $book->fresh()->status);
    }

    /**
     * TEST: Menutup catch block pada index (Baris 73-82)
     */
    /**
     * TEST: Menutup catch block pada index (Baris 73-82)
     * Strategi: Gunakan DB::shouldReceive secara parsial 
     */
  #[Test]
    public function test_index_returns_empty_on_exception()
    {
        // Gunakan spy agar tidak perlu mendefinisikan semua method
        $spy = \Mockery::spy('Illuminate\Database\DatabaseManager');
        $spy->shouldReceive('table')->andThrow(new \Exception("Query Error"));
        DB::swap($spy);

        $response = $this->get(route('hrd.kita.index'));

        $response->assertStatus(200)
            ->assertInertia(fn (Inertia $page) => $page
                ->component('app/home/kita-page')
                ->where('submissions', [])
            );
    }

 /**
     * TEST: Menutup catch block pada storePencairan (Baris 155-164)
     * Teknik: Menggunakan Model Event untuk melempar Exception secara natural
     */
    #[Test]
    public function test_store_pencairan_triggers_catch_block_on_db_error()
    {
        // 1. Buat data asli agar validator 'exists' lolos
        $book = BookSubmission::factory()->create(['status' => 'APPROVED_CHIEF']);

        // 2. Gunakan Model Event untuk melempar Exception
        // Ini akan dieksekusi tepat saat Controller memanggil $book->update()
        BookSubmission::saving(function ($model) use ($book) {
            if ($model->id === $book->id) {
                throw new \Exception("Simulated Database Error During Update");
            }
        });

        // 3. Eksekusi request
        $response = $this->post(route('hrd.pencairan'), [
            'book_id' => $book->id,
            'payment_date' => now()->format('Y-m-d'),
        ]);

        // 4. Verifikasi
        // Response harus 302 karena controller menangkap error dan melakukan return back()
        $response->assertStatus(302);
        $response->assertSessionHas('error');
        
        $errorMessage = session('error');
        $this->assertStringContainsString('Gagal memproses pencairan', $errorMessage);
        
        // Pastikan status TIDAK berubah menjadi PAID karena sudah di-rollback
        $this->assertEquals('APPROVED_CHIEF', $book->fresh()->status);

        // Bersihkan event listener agar tidak mengganggu test lain
        BookSubmission::flushEventListeners();
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}