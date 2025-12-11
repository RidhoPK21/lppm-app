<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // <--- PENTING

class Notification extends Model
{
    use HasFactory, HasUuids; // <--- PENTING

    protected $table = 'notifications';

    // Guarded kosong agar bisa mass assignment semua kolom
    protected $guarded = [];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
