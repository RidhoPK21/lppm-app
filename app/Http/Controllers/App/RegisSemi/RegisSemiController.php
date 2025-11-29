<?php

namespace App\Http\Controllers\App\RegisSemi;

use App\Http\Controllers\Controller;
use App\Models\BookSubmission;
use App\Models\SubmissionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RegisSemiController extends Controller
{
    // Menampilkan daftar pengajuan masuk (Inbox LPPM)
    public function index()
    {
        // Ambil semua pengajuan yang SUDAH dikirim dosen (Bukan Draft)
        // Diurutkan dari yang terbaru
        $submissions = BookSubmission::with('user') // Eager load data dosen
            ->where('status', '!=', 'DRAFT')
            ->orderBy('created_at', 'desc')
            ->get();

        // Mapping data agar sesuai tampilan Frontend
        $mappedData = $submissions->map(function ($item) {
            return [
                'id' => $item->id,
                'judul' => $item->title,
                'nama_dosen' => $item->user->name ?? 'Unknown User',
                'tanggal_pengajuan' => $item->created_at->format('d M Y'),
                'status' => $item->status,
                // Label status yang lebih ramah dibaca
                'status_label' => $this->formatStatusLabel($item->status),
            ];
        });

        return Inertia::render('app/RegisSemi/Index', [
            'pageName' => 'Daftar Penghargaan Masuk',
            'submissions' => $mappedData, // Kirim data asli ke React
        ]);
    }

    // Menampilkan detail buku untuk diperiksa LPPM
    public function show($id)
    {
        $book = BookSubmission::with(['authors', 'user', 'reviewers'])->findOrFail($id);

        return Inertia::render('app/RegisSemi/Detail', [
            'pageName' => 'Detail Verifikasi Buku',
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'publisher' => $book->publisher,
                'publisher_level' => $book->publisher_level,
                'year' => $book->publication_year,
                'total_pages' => $book->total_pages,
                'drive_link' => json_decode($book->drive_link), // Decode JSON link
                'status' => $book->status,
                'status_label' => $this->formatStatusLabel($book->status),
                'dosen' => $book->user->name,
                'authors' => $book->authors,
                'reviewers' => $book->reviewers,
                'approved_amount' => $book->approved_amount,
            ]
        ]);
    }

    // Action: Ketua LPPM Menyetujui Pengajuan
    public function approve(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0', // Nominal wajib diisi
        ]);

        $book = BookSubmission::findOrFail($id);

        DB::transaction(function () use ($book, $request) {
            // 1. Update Status & Nominal
            $book->update([
                'status' => 'APPROVED_CHIEF',
                'approved_amount' => $request->amount,
            ]);

            // 2. Catat Log
            SubmissionLog::create([
                'book_submission_id' => $book->id,
                'user_id' => '12e091b8-f227-4a58-8061-dc4a100c60f1', // Hardcode ID Ketua (Ganti Auth::id() nanti)
                'action' => 'APPROVE',
                'note' => 'Pengajuan disetujui oleh Ketua LPPM. Menunggu pencairan HRD.'
            ]);
        });

        return redirect()->route('app.regis-semi.index')
            ->with('success', 'Pengajuan berhasil disetujui dan diteruskan ke HRD.');
    }

    // Action: LPPM Menolak/Minta Revisi
    public function reject(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string|max:500', // Alasan penolakan wajib
        ]);

        $book = BookSubmission::findOrFail($id);

        $book->update([
            'status' => 'REJECTED', // Atau 'REVISION_REQUIRED'
        ]);

        SubmissionLog::create([
            'book_submission_id' => $book->id,
            'user_id' => '12e091b8-f227-4a58-8061-dc4a100c60f1',
            'action' => 'REJECT',
            'note' => $request->note
        ]);

        return redirect()->route('app.regis-semi.index')
            ->with('success', 'Pengajuan dikembalikan ke Dosen.');
    }

    // Helper untuk label status
    private function formatStatusLabel($status)
    {
        return match ($status) {
            'SUBMITTED' => 'Menunggu Verifikasi',
            'VERIFIED_STAFF' => 'Review Ketua',
            'APPROVED_CHIEF' => 'Disetujui (Ke HRD)',
            'PAID' => 'Selesai (Cair)',
            'REJECTED' => 'Ditolak/Revisi',
            default => $status,
        };
    }
}