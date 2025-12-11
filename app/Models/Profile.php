<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory; // <--- Import
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'profiles';

    protected $fillable = [
        'user_id',
        'name',       // Nama Lengkap dengan Gelar
        'nidn',
        'prodi',      // Program Studi
        'sinta_id',
        'scopus_id',
        // 'google_scholar_id', // Opsional: Tambahkan jika perlu
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke User (Pemilik Profil)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
