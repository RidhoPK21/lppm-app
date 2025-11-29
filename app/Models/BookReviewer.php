<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookReviewer extends Model
{
    // Sesuaikan nama tabel jika berbeda
    protected $table = 'book_reviewers'; 

    protected $fillable = [
        'book_submission_id',
        'user_id', // ID Dosen Reviewer
        'note',
        'status', // e.g., PENDING, APPROVED, REJECTED
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(BookSubmission::class, 'book_submission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}