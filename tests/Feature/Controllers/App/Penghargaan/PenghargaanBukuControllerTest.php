<?php

namespace Tests\Feature\Controllers\App\Penghargaan;

use App\Http\Middleware\CheckAuthMiddleware;
use App\Models\BookSubmission;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class PenghargaanBukuControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    protected $user;
    protected $profile;

    protected function setUp(): void
    {
        parent::setUp();

        // Bypass Middleware Auth Custom
        $this->withoutMiddleware([CheckAuthMiddleware::class]);

        // 1. SETUP SCHEMA MANUAL (Sesuaikan tipe data User ID = UUID)
        
        if (!Schema::hasTable('profiles')) {
            Schema::create('profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('user_id'); // UBAH KE UUID
                $table->string('name');
                $table->string('nidn')->nullable();
                $table->string('prodi')->nullable();
                $table->string('sinta_id')->nullable();
                $table->string('scopus_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('book_submissions')) {
            Schema::create('book_submissions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id'); // UBAH KE UUID
                $table->string('title');
                $table->string('isbn')->nullable();
                $table->integer('publication_year')->nullable();
                $table->string('publisher')->nullable();
                $table->string('publisher_level')->nullable();
                $table->string('book_type')->nullable();
                $table->integer('total_pages')->nullable();
                $table->text('drive_link')->nullable();
                $table->string('pdf_path')->nullable();
                $table->string('status')->default('DRAFT');
                $table->decimal('approved_amount', 15, 2)->nullable();
                $table->date('payment_date')->nullable();
                $table->text('reject_note')->nullable();
                $table->string('rejected_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('book_authors')) {
            Schema::create('book_authors', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('book_submission_id');
                $table->foreignUuid('user_id')->nullable(); // UBAH KE UUID
                $table->string('name');
                $table->string('role');
                $table->string('affiliation')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('submission_logs')) {
            Schema::create('submission_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('book_submission_id');
                $table->foreignUuid('user_id'); // UBAH KE UUID
                $table->string('action');
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. DATA DUMMY
        $this->user = User::factory()->create([
            'name' => 'Dosen Test',
            'email' => 'dosen@del.ac.id',
        ]);

        DB::table('profiles')->insert([
            'user_id' => $this->user->id,
            'name' => 'Dosen Test',
            'nidn' => '123456789',
            'prodi' => 'Informatika',
            'sinta_id' => 'SINTA-001',
            'scopus_id' => 'SCOPUS-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_index_displays_books_list()
    {
        BookSubmission::create([
            'user_id' => $this->user->id,
            'title' => 'Buku Algoritma',
            'status' => 'DRAFT',
            'isbn' => '123',
            'publication_year' => 2024,
            'publisher' => 'Test', 
            'publisher_level' => 'NATIONAL',
            'book_type' => 'TEACHING',
            'total_pages' => 100
        ]);

        $response = $this->actingAs($this->user)
                         ->get(route('app.penghargaan.buku.index'));

        $response->assertStatus(200)
                 ->assertInertia(fn (Assert $page) => $page
                     ->component('app/penghargaan/buku/page')
                     ->has('buku', 1)
                 );
    }

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->user)
                         ->get(route('app.penghargaan.buku.create'));
        $response->assertStatus(200);
    }

    public function test_store_saves_book_and_authors()
    {
        $data = [
            'judul' => 'Pemrograman Web Lanjut',
            'penulis' => 'Budi Santoso, Siti Aminah',
            'penerbit' => 'Penerbit IT Del',
            'tahun' => date('Y'),
            'isbn' => '978-602-123-456',
            'kategori' => 'TEACHING',
            'jumlah_halaman' => 150,
            'level_penerbit' => 'NATIONAL',
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('app.penghargaan.buku.store'), $data);

        $book = BookSubmission::where('title', 'Pemrograman Web Lanjut')->first();
        $this->assertNotNull($book, 'Book should be created in DB');
        
        $response->assertRedirect(route('app.penghargaan.buku.upload', ['id' => $book->id]));
    }

    public function test_store_validation_error()
    {
        $response = $this->actingAs($this->user)
                         ->post(route('app.penghargaan.buku.store'), []);
        
        $response->assertSessionHasErrors(['judul']);
    }

    public function test_show_detail_displays_book_and_profile()
    {
        $book = BookSubmission::create(['user_id' => $this->user->id, 'title' => 'Buku Detail', 'status' => 'DRAFT', 'isbn'=>'1', 'publication_year'=>2024, 'publisher'=>'a', 'publisher_level'=>'NATIONAL', 'book_type'=>'TEACHING', 'total_pages'=>10]);
        
        $response = $this->actingAs($this->user)
                         ->get(route('app.penghargaan.buku.detail', $book->id));
        
        $response->assertStatus(200);
    }

    public function test_upload_docs_page()
    {
        $book = BookSubmission::create(['user_id' => $this->user->id, 'title' => 'Buku Upload', 'status' => 'DRAFT', 'isbn'=>'1', 'publication_year'=>2024, 'publisher'=>'a', 'publisher_level'=>'NATIONAL', 'book_type'=>'TEACHING', 'total_pages'=>10]);
        
        $response = $this->actingAs($this->user)
                         ->get(route('app.penghargaan.buku.upload', $book->id));
        
        $response->assertStatus(200);
    }

    public function test_store_upload_saves_links_and_generates_pdf()
    {
        Storage::fake('public');
        Pdf::shouldReceive('loadView')->andReturnSelf();
        Pdf::shouldReceive('output')->andReturn('PDF_CONTENT');

        $book = BookSubmission::create(['user_id' => $this->user->id, 'title' => 'Buku PDF', 'status' => 'DRAFT', 'isbn'=>'1', 'publication_year'=>2024, 'publisher'=>'a', 'publisher_level'=>'NATIONAL', 'book_type'=>'TEACHING', 'total_pages'=>10]);
        
        $response = $this->actingAs($this->user)
                         ->post(route('app.penghargaan.buku.store-upload', $book->id), [
                             'links' => ['http://example.com/file1']
                         ]);

        $response->assertRedirect(route('app.penghargaan.buku.detail', $book->id));
    }

    public function test_submit_fails_if_not_draft()
    {
        $book = BookSubmission::create(['user_id' => $this->user->id, 'title' => 'Buku Sub', 'status' => 'SUBMITTED', 'isbn'=>'1', 'publication_year'=>2024, 'publisher'=>'a', 'publisher_level'=>'NATIONAL', 'book_type'=>'TEACHING', 'total_pages'=>10]);
        
        $response = $this->actingAs($this->user)
                         ->post(route('app.penghargaan.buku.submit', $book->id));
        
        $response->assertSessionHas('error');
    }

    public function test_submit_fails_if_documents_incomplete()
    {
        $book = BookSubmission::create(['user_id' => $this->user->id, 'title' => 'Buku Inc', 'status' => 'DRAFT', 'drive_link'=>json_encode(['a']), 'isbn'=>'1', 'publication_year'=>2024, 'publisher'=>'a', 'publisher_level'=>'NATIONAL', 'book_type'=>'TEACHING', 'total_pages'=>10]);
        
        $response = $this->actingAs($this->user)
                         ->post(route('app.penghargaan.buku.submit', $book->id));
        
        $response->assertSessionHas('error');
    }

    public function test_submit_success()
    {
        Storage::fake('public');
        Pdf::shouldReceive('loadView')->andReturnSelf();
        Pdf::shouldReceive('output')->andReturn('PDF_CONTENT');

        $links = array_fill(0, 6, 'http://valid-link.com');
        $book = BookSubmission::create(['user_id' => $this->user->id, 'title' => 'Buku Ok', 'status' => 'DRAFT', 'drive_link'=>json_encode($links), 'isbn'=>'1', 'publication_year'=>2024, 'publisher'=>'a', 'publisher_level'=>'NATIONAL', 'book_type'=>'TEACHING', 'total_pages'=>10]);
        
        $response = $this->actingAs($this->user)
                         ->post(route('app.penghargaan.buku.submit', $book->id));
        
        $response->assertRedirect(route('app.penghargaan.buku.index'));
    }

    public function test_preview_pdf_regenerates_if_missing()
    {
        Storage::fake('public');
        
        // Mock PDF lengkap (Load, Output, Stream)
        Pdf::shouldReceive('loadView')->andReturnSelf();
        Pdf::shouldReceive('output')->andReturn('PDF_CONTENT'); // Untuk generateAndSavePdf
        Pdf::shouldReceive('stream')->andReturn(response('STREAM_CONTENT')); // Untuk response ke browser

        $book = BookSubmission::create(['user_id' => $this->user->id, 'title' => 'Buku Prev', 'status' => 'DRAFT', 'pdf_path'=>'missing.pdf', 'isbn'=>'1', 'publication_year'=>2024, 'publisher'=>'a', 'publisher_level'=>'NATIONAL', 'book_type'=>'TEACHING', 'total_pages'=>10]);
        
        $response = $this->actingAs($this->user)
                         ->get(route('app.penghargaan.buku.preview-pdf', $book->id));
        
        $response->assertStatus(200);
    }

    public function test_download_pdf_success()
    {
        Storage::fake('public');
        $book = BookSubmission::create(['user_id' => $this->user->id, 'title' => 'Buku DL', 'status' => 'DRAFT', 'pdf_path'=>'test.pdf', 'isbn'=>'1', 'publication_year'=>2024, 'publisher'=>'a', 'publisher_level'=>'NATIONAL', 'book_type'=>'TEACHING', 'total_pages'=>10]);
        
        Storage::disk('public')->put('test.pdf', 'CONTENT');
        
        $response = $this->actingAs($this->user)
                         ->get(route('app.penghargaan.buku.download-pdf', $book->id));
        
        $response->assertStatus(200);
    }

    public function test_download_pdf_not_found()
    {
        $book = BookSubmission::create(['user_id' => $this->user->id, 'title' => 'Buku Fail', 'status' => 'DRAFT', 'pdf_path'=>null, 'isbn'=>'1', 'publication_year'=>2024, 'publisher'=>'a', 'publisher_level'=>'NATIONAL', 'book_type'=>'TEACHING', 'total_pages'=>10]);
        
        $response = $this->actingAs($this->user)
                         ->get(route('app.penghargaan.buku.download-pdf', $book->id));
        
        $response->assertSessionHas('error');
    }
}