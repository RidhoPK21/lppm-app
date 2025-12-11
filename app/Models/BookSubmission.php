<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookSubmission extends Model
{
    use HasFactory;

    protected $table = 'book_submissions';

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
        'pdf_path', // 🔥 TAMBAHKAN INI
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi dengan SubmissionLog
    public function logs()
    {
        return $this->hasMany(SubmissionLog::class, 'book_submission_id');
    }
}
