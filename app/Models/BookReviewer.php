<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // 1. Import trait HasUuids
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookReviewer extends Model
{
    use HasFactory, HasUuids; // 2. Gunakan trait HasUuids

    // 3. Matikan auto-increment
    public $incrementing = false;

    // 4. Set tipe key menjadi string
    protected $keyType = 'string';

    protected $table = 'book_reviewers';

    protected $fillable = [
        'book_submission_id',
        'user_id',
        'note',
        'status',
        'reviewed_at',
        'invited_by',
        'invited_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'invited_at' => 'datetime',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(BookSubmission::class, 'book_submission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'ACCEPTED');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }
}