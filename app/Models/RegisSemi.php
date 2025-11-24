<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisSemi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_seminar',
        'penyelenggara',
        'tanggal_seminar',
        'lokasi',
    ];

    protected $table = 'regis_semis';
}