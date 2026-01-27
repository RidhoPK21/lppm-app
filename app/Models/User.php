<?php

namespace App\Models;

// Hapus 'Laravel\Sanctum\HasApiTokens'
use Illuminate\Database\Eloquent\Concerns\HasUuids; // <--- WAJIB ADA
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // Gunakan trait HasUuids agar ID tergenerate otomatis
    use HasFactory, Notifiable, HasUuids; 

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // =================================================================
    // 🔥 PERBAIKAN UTAMA: KONFIGURASI UUID
    // Tanpa ini, relasi ke m_hak_akses akan SELALU GAGAL (Return kosong)
    // =================================================================
    protected $keyType = 'string'; // Memberitahu Laravel ID adalah String
    public $incrementing = false;  // Mematikan auto-increment angka
    // =================================================================

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ✅ JEMBATAN KE DATA HAK AKSES
    public function hakAkses()
    {
        return $this->hasMany(HakAksesModel::class, 'user_id', 'id');
    }
}