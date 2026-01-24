<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarModel extends Model
{
    use HasFactory;

    protected $table = 't_seminar';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'kewajiban_penelitian' => 'array', // Auto convert JSON ke Array
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    // ==========================================
    // RELASI
    // ==========================================

    public function dosen()
    {
        return $this->belongsTo(ProfilDosenModel::class, 'dosen_profil_id');
    }

    public function penulis()
    {
        return $this->hasMany(SeminarPenulisModel::class, 'seminar_id');
    }
    
    public function reviews()
    {
        return $this->hasMany(SeminarReviewModel::class, 'seminar_id');
    }
}