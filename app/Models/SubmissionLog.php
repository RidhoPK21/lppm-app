<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionLog extends Model
{
    use HasFactory;

    protected $table = 'submission_logs';

    protected $fillable = [
        'book_submission_id',
        'user_id',
        'action',
        'note',
    ];
}
