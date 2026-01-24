<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarPenulisModel extends Model
{
    use HasFactory;
    
    // Sesuai standar tabel transaksi
    protected $table = 't_seminar_penulis';
    protected $primaryKey = 'id';
    protected $keyType = 'string'; // UUID
    public $incrementing = false;
    protected $guarded = [];
    public $timestamps = true;

    public function seminar()
    {
        return $this->belongsTo(SeminarModel::class, 'seminar_id');
    }
}