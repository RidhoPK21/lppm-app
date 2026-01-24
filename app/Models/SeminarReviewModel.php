<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarReviewModel extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 't_seminar_review';

    // Primary Key bertipe String (UUID)
    protected $keyType = 'string';
    public $incrementing = false;

    // 🔥 PERBAIKAN DISINI: Sesuaikan dengan nama kolom Database
    protected $fillable = [
        'id',
        'seminar_id',
        'reviewer_id',
        'status',           // 'menunggu', 'proses', 'menunggu_perbaikan', 'selesai', 'ditolak'
        'catatan_review',   // ✅ GANTI 'catatan' MENJADI 'catatan_review'
        'balasan_revisi', // <--- TAMBAHKAN INI
        'keputusan',        // Tambahkan ini jika Anda menyimpan status accept/reject
        'revisi_ke',        // Tambahkan ini untuk tracking revisi
        'tanggal_review',
        'file_revisi'
    ];

    // ===========================
    // RELASI
    // ===========================

    // Relasi ke Seminar (Makalah)
    public function seminar()
    {
        return $this->belongsTo(SeminarModel::class, 'seminar_id', 'id');
    }

    // Relasi ke Profil Dosen (sebagai Reviewer)
    public function reviewer()
    {
        return $this->belongsTo(ProfilDosenModel::class, 'reviewer_id', 'id');
    }
}