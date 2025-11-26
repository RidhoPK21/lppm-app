<?php

namespace App\Http\Controllers\App\Penghargaan;

use App\Http\Controllers\Controller;
use App\Models\BookSubmission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminPenghargaanBukuController extends Controller
{
    public function index(Request $request)
    {
        // Ambil parameter search jika admin ingin mencari buku
        $search = $request->input('search');

        $query = BookSubmission::with(['user', 'authors'])
            // HANYA ambil yang statusnya BUKAN Draft (artinya sudah dikirim dosen)
            ->where('status', '!=', 'DRAFT');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        // Urutkan dari yang terbaru masuk
        $submissions = $query->orderBy('updated_at', 'desc')->get();

        // Mapping data agar bersih saat masuk ke React
        $mappedSubmissions = $submissions->map(function ($book) {
            // Ambil nama penulis pertama
            $firstAuthor = $book->authors->where('role', 'FIRST_AUTHOR')->first();
            $authorName = $firstAuthor ? $firstAuthor->name : ($book->authors->first()->name ?? '-');
            
            // Hitung sisa penulis
            $countOthers = $book->authors->count() - 1;
            if ($countOthers > 0) {
                $authorName .= " + {$countOthers} lainnya";
            }

            return [
                'id' => $book->id,
                'judul' => $book->title,
                'pengusul' => $book->user->name ?? 'Unknown',
                'penulis_display' => $authorName,
                'isbn' => $book->isbn,
                'tanggal_masuk' => $book->updated_at->format('d M Y'),
                'status' => $book->status,
                'status_label' => $this->formatStatusLabel($book->status),
                'status_color' => $this->getStatusColor($book->status), // Untuk warna Badge
            ];
        });

        return Inertia::render('app/admin/penghargaan/buku/index', [
            'pageName' => 'Penghargaan Buku Masuk',
            'submissions' => $mappedSubmissions,
            'filters' => $request->only(['search']),
        ]);
    }

    // Helper Warna Badge
    private function getStatusColor($status)
    {
        return match ($status) {
            'SUBMITTED' => 'bg-blue-100 text-blue-800 border-blue-200',
            'VERIFIED_STAFF' => 'bg-yellow-100 text-yellow-800 border-yellow-200', // Menunggu Ketua
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