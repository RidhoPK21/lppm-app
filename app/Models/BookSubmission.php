<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory; // <--- Import
use Illuminate\Database\Eloquent\Model;

class BookSubmission extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'book_submissions';

    // Konstanta Status agar tidak hardcode string di Controller/View
    public const STATUS_DRAFT = 'DRAFT';

    public const STATUS_SUBMITTED = 'SUBMITTED';

    public const STATUS_IN_REVIEW = 'IN_REVIEW';

    public const STATUS_REJECTED = 'REJECTED';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_PAID = 'PAID';

    protected $fillable = [
        'user_id',
        'title',
        'isbn',
        'publication_year',
        'publisher',
        'publisher_level',
        'book_type',
        'total_pages',
        'drive_link',
        'pdf_path', // 🔥 Pastikan folder storage sudah di-link (php artisan storage:link)
        'approved_amount',
        'payment_date',
        'reject_note',
        'status',
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'total_pages' => 'integer',
        'approved_amount' => 'decimal:2',
        'payment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi dengan BookAuthor
    public function authors()
    {
        return $this->hasMany(BookAuthor::class, 'book_submission_id');
    }

    // Relasi dengan User (Pengusul)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi dengan Reviewer (YANG DITAMBAHKAN)
    public function reviewers()
    {
        return $this->hasMany(BookReviewer::class, 'book_submission_id');
    }

    // Relasi dengan SubmissionLog
    public function logs()
    {
        return $this->hasMany(SubmissionLog::class, 'book_submission_id');
    }
}
