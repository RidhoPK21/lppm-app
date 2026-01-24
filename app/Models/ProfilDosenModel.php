<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilDosenModel extends Model
{
    use HasFactory;
    
    protected $table = 'm_profil_dosen'; 
    
    protected $primaryKey = 'id';
    protected $keyType = 'string'; 
    public $incrementing = false;
    
    public $timestamps = true;

    // 🔥 KITA GUNAKAN FILLABLE AGAR LEBIH PASTI AMAN
    protected $fillable = [
        'id',
        'user_id',
        'nidn',
        'prodi',
        'jabatan_fungsional',
        'status_aktif',
        'sinta_id',           // <--- Wajib ada
        'scopus_id',          // <--- Wajib ada    
    ];

    // Tambahkan relasi ke ProfilDosenModel (dari Porman)
public function profilDosen()
{
    return $this->hasOne(ProfilDosenModel::class, 'user_id', 'id');
}

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
