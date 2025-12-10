<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // 1. Import trait HasUuids
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionLog extends Model
{
    use HasFactory, HasUuids; // 2. Gunakan trait HasUuids

    // 3. Matikan auto-increment
    public $incrementing = false;

    // 4. Set tipe key menjadi string
    protected $keyType = 'string';

    protected $table = 'submission_logs';

    protected $fillable = [
        'book_submission_id',
        'user_id',
        'action',
        'note',
    ];
}