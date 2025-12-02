<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HakAksesModel extends Model
{
    protected $table = 'm_hak_akses';

    protected $primaryKey = 'id';
    public $incrementing = false;     // ⬅ WAJIB untuk UUID
    protected $keyType = 'string';    // ⬅ UUID bukan integer

    protected $fillable = [
        'id',
        'user_id',
        'akses',
    ];

    public $timestamps = true;

    // Generate UUID otomatis jika tidak dikirim
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
