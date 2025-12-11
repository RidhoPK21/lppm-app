<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookAuthor extends Model
{
    use HasFactory;

    protected $table = 'book_authors';

    protected $fillable = [
        'book_submission_id',
        'user_id',
        'name',
        'role',
        'affiliation'
    ];

    // Relasi balik ke Buku
    public function book()
    {
        return $this->belongsTo(BookSubmission::class, 'book_submission_id');
    }
}