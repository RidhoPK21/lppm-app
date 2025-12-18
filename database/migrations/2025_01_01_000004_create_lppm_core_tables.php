<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. book_submissions
        Schema::create('book_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index(); // Pemilik pengajuan
            $table->string('title');
            $table->string('isbn', 50);
            $table->integer('publication_year');
            $table->string('publisher');
            $table->enum('publisher_level', ['NATIONAL', 'INTERNATIONAL', 'NATIONAL_ACCREDITED']);
            $table->enum('book_type', ['TEACHING', 'REFERENCE', 'MONOGRAPH', 'CHAPTER']);
            $table->integer('total_pages');
            $table->text('drive_link')->nullable();
            $table->string('pdf_path')->nullable();
            $table->decimal('approved_amount', 15, 2)->nullable();
            $table->date('payment_date')->nullable();
            $table->text('reject_note')->nullable();
            $table->uuid('rejected_by')->nullable();
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'REVISION_REQUIRED', 'VERIFIED_STAFF', 'APPROVED_CHIEF', 'REJECTED', 'PAID']);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 2. book_authors
        Schema::create('book_authors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('book_submission_id')->index();
            $table->uuid('user_id')->nullable();
            $table->string('name');
            $table->enum('role', ['FIRST', 'MEMBER', 'CORRESPONDING']);
            $table->string('affiliation')->nullable();
            $table->timestamps();

            $table->foreign('book_submission_id')->references('id')->on('book_submissions')->onDelete('cascade');
        });

        // 3. submission_logs
        Schema::create('submission_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('book_submission_id')->index();
            $table->uuid('user_id');
            $table->string('action', 50);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('book_submission_id')->references('id')->on('book_submissions')->onDelete('cascade');
        });
        
        // 4. book_reviewers (Opsional, tapi sebaiknya dimasukkan)
        Schema::create('book_reviewers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('book_submission_id')->index();
            $table->uuid('user_id');
            $table->text('note')->nullable();
            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED']);
            $table->timestamp('reviewed_at')->nullable();
            $table->uuid('invited_by')->nullable();
            $table->timestamp('invited_at')->nullable();
            $table->timestamps();

            $table->foreign('book_submission_id')->references('id')->on('book_submissions')->onDelete('cascade');
        });

        // 5. notifications (Opsional, tapi sebaiknya dimasukkan)
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->string('title', 255);
            $table->text('message');
            $table->enum('type', ['Info', 'Sukses', 'Peringatan', 'Error', 'System']);
            $table->boolean('is_read')->default(false);
            $table->string('reference_key', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('book_reviewers');
        Schema::dropIfExists('submission_logs');
        Schema::dropIfExists('book_authors');
        Schema::dropIfExists('book_submissions');
    }
};