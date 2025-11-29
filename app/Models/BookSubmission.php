<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookSubmission extends Model
{
    use HasFactory;

    // PENTING: Definisikan nama tabel secara eksplisit agar sesuai dengan lppm-models
    protected $table = 'book_submissions';

    // Izinkan mass assignment untuk kolom-kolom ini
    protected $fillable = [
        'user_id',
        'title',
        'isbn',
        'publication_year',
        'publisher',
        'publisher_level',
        'book_type',
        'total_pages',
       // 'file_path',
        'drive_link',
        'status',
        'approved_amount'
    ];

    // Relasi: Satu buku dimiliki satu User (Dosen)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Satu buku punya banyak Penulis
    public function authors(): HasMany
    {
        return $this->hasMany(BookAuthor::class, 'book_submission_id');
    }

    // Relasi: Satu buku punya banyak Log History
    public function logs(): HasMany
    {
        return $this->hasMany(SubmissionLog::class, 'book_submission_id');
    }

    public function reviewers(): HasMany
    {
        // Pastikan Anda sudah punya model BookReviewer
        // Jika belum punya, lihat langkah ke-2 di bawah
        return $this->hasMany(BookReviewer::class, 'book_submission_id');
    }
}