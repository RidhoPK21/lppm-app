<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // 1. Import trait HasUuids
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory, HasUuids; // 2. Gunakan trait HasUuids

    // 3. Matikan auto-increment
    public $incrementing = false;

    // 4. Set tipe key menjadi string
    protected $keyType = 'string';

    protected $table = 'profiles';

    protected $fillable = [
        'user_id',
        'name',
        'nidn',
        'prodi',
        'sinta_id',
        'scopus_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}