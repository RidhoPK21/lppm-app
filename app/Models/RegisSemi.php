<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // 1. Import trait HasUuids
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisSemi extends Model
{
    use HasFactory, HasUuids; // 2. Gunakan trait HasUuids

    // 3. Matikan auto-increment
    public $incrementing = false;

    // 4. Set tipe key menjadi string
    protected $keyType = 'string';

    // Gunakan tabel yang sudah ada: book_reviewers
    protected $table = 'book_reviewers';

    protected $fillable = [
        'book_submission_id',
        'user_id',
        'review_note', // Pastikan nama kolom ini sesuai dengan database (mungkin 'note'?)
        'review_date', // Pastikan nama kolom ini sesuai dengan database (mungkin 'reviewed_at'?)
        'status',
        // Tambahkan field untuk invited_at jika diperlukan
    ];

    protected $casts = [
        'review_date' => 'datetime',
        'invited_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    /**
     * Set default values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Jika status tidak diisi, set default
            if (! $model->status) {
                $model->status = 'PENDING';
            }
            // Set invited_at jika membuat undangan baru
            if (! $model->invited_at && $model->status === 'PENDING') {
                $model->invited_at = now();
            }
        });
    }
}