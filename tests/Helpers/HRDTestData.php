<?php

namespace Tests\Helpers;

class HRDTestData
{
    public static function getApprovedChiefBooks()
    {
        return collect([
            (object) [
                'id' => '550e8400-e29b-41d4-a716-446655440000',
                'judul' => 'Buku Penelitian Teknologi',
                'approved_amount' => 2500000.00,
                'tanggal_pengajuan' => '2024-03-10 10:30:00',
                'status' => 'APPROVED_CHIEF',
                'nama_dosen' => 'Dr. Ahmad Wijaya, M.Kom.',
            ],
            (object) [
                'id' => '550e8400-e29b-41d4-a716-446655440001',
                'judul' => 'Buku Pendidikan Karakter',
                'approved_amount' => 3000000.00,
                'tanggal_pengajuan' => '2024-03-12 14:20:00',
                'status' => 'APPROVED_CHIEF',
                'nama_dosen' => 'Dr. Siti Fatimah, M.Pd.',
            ]
        ]);
    }
    
    public static function getEmptyBooks()
    {
        return collect([]);
    }
    
    public static function getBookSubmissionMock()
    {
        return [
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'title' => 'Buku Test Mock',
            'user_id' => 'user-uuid-123',
            'status' => 'APPROVED_CHIEF',
            'approved_amount' => 2000000.00,
        ];
    }
}