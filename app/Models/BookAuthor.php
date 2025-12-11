<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory; // <--- Import
use Illuminate\Database\Eloquent\Model;

class BookAuthor extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'book_authors';

    protected $fillable = [
        'book_submission_id',
        'user_id',    // Biasanya nullable (jika penulis eksternal/bukan user sistem)
        'name',       // Nama penulis (bisa diambil dari User atau input manual)
        'role',       // Misal: 'Penulis Utama', 'Anggota', 'Korespondensi'
        'affiliation',
    ];

    /**
     * Relasi ke Buku (Submission).
     */
    public function book()
    {
        return $this->belongsTo(BookSubmission::class, 'book_submission_id');
    }

    /**
     * Relasi ke User (Penulis Internal).
     * Tambahan: Ini penting jika author adalah dosen/staf yang punya akun.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
