<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // 1. Import trait HasUuids
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookSubmission extends Model
{
    use HasFactory, HasUuids; // 2. Gunakan trait HasUuids di sini

    // 3. Matikan auto-increment (karena UUID tidak auto-increment)
    public $incrementing = false;

    // 4. Set tipe key menjadi string (karena UUID adalah string)
    protected $keyType = 'string';

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
        'pdf_path',
        'approved_amount',
        'payment_date',
        'reject_note',
        'rejected_by', // Pastikan kolom ini juga ada di fillable
        'status',
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'total_pages' => 'integer',
        'approved_amount' => 'decimal:2',
        'payment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'drive_link' => 'array', // Opsional: jika drive_link disimpan sebagai JSON
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