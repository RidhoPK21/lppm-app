<?php

namespace App\Http\Controllers\App\Penghargaan;

use App\Http\Controllers\Controller;
use App\Models\BookSubmission;
use App\Models\HakAksesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AdminPenghargaanBukuController extends Controller
{
     public function index(Request $request)
    {
        $search = $request->input('search');

        // HAPUS with(['user']) - hanya pakai authors saja
        $query = BookSubmission::with(['authors'])
            ->where('status', '!=', 'DRAFT');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('user_id', 'like', "%{$search}%");
            });
        }

        // Ambil user_id yang memiliki akses Dosen (termasuk yang punya akses lain)
        $dosenUserIds = HakAksesModel::getUserIdsWithDosenAkses();

        // Debug info
        Log::info('Admin Penghargaan Buku - Dosen Filter', [
            'total_dosen_ids' => count($dosenUserIds),
            'sample_ids' => array_slice($dosenUserIds, 0, 5)
        ]);

        if (!empty($dosenUserIds)) {
            $query->whereIn('user_id', $dosenUserIds);
        } else {
            $query->where('user_id', null);
        }

        $submissions = $query->orderBy('updated_at', 'desc')->get();

        $mappedSubmissions = $submissions->map(function ($book) {
            $firstAuthor = $book->authors->where('role', 'FIRST_AUTHOR')->first();
            $authorName = $firstAuthor ? $firstAuthor->name : ($book->authors->first()->name ?? '-');
            $countOthers = $book->authors->count() - 1;
            if ($countOthers > 0) {
                $authorName .= " + {$countOthers} lainnya";
            }

            // Ambil akses user untuk informasi tambahan
            $userAkses = HakAksesModel::getAksesByUserId($book->user_id);
            $hasDosenAkses = in_array('Dosen', $userAkses);

            return [
                'id' => $book->id,
                'judul' => $book->title,
                'user_id' => $book->user_id,
                'nama_dosen' => 'Dosen (' . substr($book->user_id, 0, 8) . ')',
                'penulis_display' => $authorName,
                'isbn' => $book->isbn,
                'tanggal_pengajuan' => $book->updated_at->format('d M Y'),
                'status' => $book->status,
                'status_label' => $this->formatStatusLabel($book->status),
                'status_color' => $this->getStatusColor($book->status),
                'user_akses' => $userAkses, // Informasi tambahan
                'has_dosen_akses' => $hasDosenAkses, // Flag
            ];
        });

        return Inertia::render('app/admin/penghargaan/buku/index', [
            'pageName' => 'Penghargaan Buku Masuk',
            'submissions' => $mappedSubmissions,
            'filters' => $request->only(['search']),
            'stats' => [
                'total_submissions' => $mappedSubmissions->count(),
                'with_dosen_akses' => $mappedSubmissions->where('has_dosen_akses', true)->count(),
                'unique_dosen' => count(array_unique($mappedSubmissions->pluck('user_id')->toArray())),
            ]
        ]);
    }

    private function getStatusColor($status)
    {
        return match ($status) {
            'SUBMITTED' => 'bg-blue-100 text-blue-800 border-blue-200',
            'VERIFIED_STAFF' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'APPROVED_CHIEF' => 'bg-green-100 text-green-800 border-green-200',
            'REJECTED', 'REVISION_REQUIRED' => 'bg-red-100 text-red-800 border-red-200',
            'PAID' => 'bg-gray-100 text-gray-800 border-gray-200',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    private function formatStatusLabel($status)
    {
        return match ($status) {
            'SUBMITTED' => 'Perlu Verifikasi Staff',
            'VERIFIED_STAFF' => 'Menunggu Approval Ketua',
            'REVISION_REQUIRED' => 'Revisi Diperlukan',
            'APPROVED_CHIEF' => 'Disetujui',
            'REJECTED' => 'Ditolak',
            'PAID' => 'Selesai Cair',
            default => $status,
        };
    }
}