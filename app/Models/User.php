<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
// 🔥 WAJIB: Impor HasOne untuk relasi HakAkses
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id', // Diperlukan untuk factory/UUID
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke Model HakAkses (Asumsi 1:1)
     */
    public function hakAkses(): HasOne
    {
        return $this->hasOne(HakAksesModel::class, 'user_id', 'id');
    }

    /**
     * Accessor untuk mendapatkan peran (akses) user
     * Ini yang kemungkinan dipanggil oleh middleware CheckRole.
     */
    public function getAksesAttribute(): string
    {
        // Memuat relasi hakAkses dan mengembalikan nilai 'akses', atau string kosong
        return $this->hakAkses->akses ?? '';
    }
}
