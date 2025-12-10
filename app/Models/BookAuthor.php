<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // 1. Import trait HasUuids
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookAuthor extends Model
{
    use HasFactory, HasUuids; // 2. Gunakan trait HasUuids

    // 3. Matikan auto-increment
    public $incrementing = false;

    // 4. Set tipe key menjadi string
    protected $keyType = 'string';

    protected $table = 'book_authors';

    protected $fillable = [
        'book_submission_id',
        'user_id',
        'name',
        'role',
        'affiliation',
    ];

    // Relasi balik ke Buku
    public function book()
    {
        return $this->belongsTo(BookSubmission::class, 'book_submission_id');
    }
}