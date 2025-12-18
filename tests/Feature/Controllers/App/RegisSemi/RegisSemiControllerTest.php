<?php

namespace Tests\Feature\Controllers\App\RegisSemi;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Inertia; // 🔥 Tambahkan import ini jika Anda ingin menguji Inertia
use Illuminate\Support\Facades\Log; // 🔥 Tambahkan ini untuk debugging

class RegisSemiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $lppmStaff;
    protected $dosenUser;
    protected $ketuaLppm;

    // --- SETUP HELPER METHODS (Disederhanakan sedikit) ---

    protected function createLocalUser(array $attributes = []): \App\Models\User
    {
        // Pastikan Anda mengimpor App\Models\User di atas (asumsi sudah)
        $user = new \App\Models\User();
        $user->id = $attributes['id'] ?? (string) Str::uuid();
        $user->name = $attributes['name'] ?? 'Test User';
        $user->email = $attributes['email'] ?? 'test.' . Str::random(5) . '@example.com';
        $user->password = bcrypt('password');
        $user->email_verified_at = now();
        $user->remember_token = Str::random(10);
        $user->save();
        return $user;
    }
    
    protected function addLocalHakAkses(string $userId, string $akses): void
    {
        // Perlu dipastikan tabel m_hak_akses sudah ada (asumsi migrasi sudah benar)
        DB::table('m_hak_akses')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'akses' => $akses,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    // DI DALAM RegisSemiControllerTest.php
protected function createLocalBookSubmission(array $attributes = []): \App\Models\BookSubmission
{
    $submission = new \App\Models\BookSubmission();
    // ... (kode lainnya)
    $submission->isbn = $attributes['isbn'] ?? '111-2223334444';
    $submission->publication_year = $attributes['publication_year'] ?? 2024;
    
    // 🔥 PERBAIKAN: TAMBAHKAN KOLOM WAJIB INI
    $submission->publisher_level = $attributes['publisher_level'] ?? 'NATIONAL'; // <-- Tambahkan nilai default
    
    $submission->save();
    return $submission;
}
    
    // --- SETUP TEST ENVIRONMENT ---

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->withoutMiddleware();
        
        // Buat semua user dan tambahkan Hak Akses
        $this->lppmStaff = $this->createLocalUser(['name' => 'Staff LPPM', 'email' => 'staff.lppm@example.com']);
        $this->dosenUser = $this->createLocalUser(['name' => 'Prof. Dosen', 'email' => 'dosen@example.com']);
        $this->ketuaLppm = $this->createLocalUser(['name' => 'Ketua LPPM', 'email' => 'ketua.lppm@example.com']);
        
        $this->addLocalHakAkses($this->lppmStaff->id, 'Lppm Staff');
        $this->addLocalHakAkses($this->dosenUser->id, 'DOSEN');
        $this->addLocalHakAkses($this->ketuaLppm->id, 'Lppm Ketua');
        
        $this->actingAs($this->lppmStaff);
    }
    
    // --- TEST METHODS ---

    /** @test */
    public function test_index_displays_submissions(): void
    {
        // ARRANGE
        $this->createLocalBookSubmission([
            'title' => 'Active Book',
            'status' => 'SUBMITTED',
        ]);
        
        // ACT
        $response = $this->get(route('regis-semi.index'));
        
        // ASSERT
        $response->assertStatus(200);
        // Jika Anda menggunakan Inertia, gunakan assertion Inertia
        $response->assertInertia(fn (Inertia $page) => $page->has('submissions', 1));
        
        Log::info("✓ Regis Semi index accessible");
    }
    
    /** @test */
    public function test_approve_flow_updates_status_and_amount(): void
    {
        // ARRANGE
        // Pastikan status VERIFIED_STAFF agar Ketua LPPM bisa approve
        $book = $this->createLocalBookSubmission([
            'title' => 'Book to Approve',
            'status' => 'VERIFIED_STAFF',
        ]);
        
        // ACT
        $this->actingAs($this->ketuaLppm); // Switch to ketua LPPM
        $approvedAmount = 5000000;
        
        // Kita juga perlu mock NotificationController secara alias agar tidak crash/dilewati
        \Mockery::mock('alias:\App\Http\Controllers\App\Notifikasi\NotificationController')
            ->shouldReceive('sendBookPaymentNotification')
            ->once()
            ->andReturn(true);

        $response = $this->post(route('regis-semi.approve', $book->id), [
            'amount' => $approvedAmount,
        ]);
        
        // ASSERT
        $response->assertStatus(302);
        $response->assertSessionHas('success');
        
        // Cek database, jangan update manual
        $this->assertDatabaseHas('book_submissions', [
            'id' => $book->id,
            'status' => 'APPROVED_CHIEF', // Perubahan status dari controller
            'approved_amount' => $approvedAmount, // Perubahan amount dari controller
        ]);

        Log::info("✓ Approve flow test passed");
    }
}