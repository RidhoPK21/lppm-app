<?php

namespace App\Http\Controllers\App\RegisSemi;

use App\Http\Controllers\Controller;
use App\Models\BookSubmission;
use App\Models\SubmissionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\User;
use App\Models\BookReviewer;

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

    public function show($id)
{
    // HAPUS pemanggilan 'reviewers' agar tidak error 500
    $book = BookSubmission::with(['authors', 'user'])->findOrFail($id);

    return Inertia::render('app/RegisSemi/Detail', [
        'pageName' => 'Detail Verifikasi Buku',
        'book' => [
            'id' => $book->id,
            'title' => $book->title,
            'isbn' => $book->isbn,
            'publisher' => $book->publisher,
            'drive_link' => json_decode($book->drive_link),
            'status_label' => $book->status,
            // Gunakan tanda tanya (?) agar tidak error jika user terhapus
            'dosen' => $book->user->name ?? 'Dosen Tidak Ditemukan', 
        ]
    ]);
}

public function invite($id)
    {
        $book = BookSubmission::findOrFail($id);

        // Ambil semua User (Dosen) KECUALI pengusul buku itu sendiri
        // Idealnya difilter berdasarkan role 'DOSEN' atau 'REVIEWER' dari tabel hak akses
        $users = User::where('id', '!=', $book->user_id)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($user) use ($book) {
                // Cek apakah user ini sudah pernah diundang sebelumnya
                $isInvited = BookReviewer::where('book_submission_id', $book->id)
                    ->where('user_id', $user->id)
                    ->exists();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'initial' => substr($user->name, 0, 2), // Untuk Avatar
                    'is_invited' => $isInvited // Status apakah sudah diundang
                ];
            });

        return Inertia::render('app/RegisSemi/Invite', [
            'book' => $book,
            'availableReviewers' => $users
        ]);
    }

    // 2. PROSES SIMPAN UNDANGAN
    public function storeInvite(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Simpan ke tabel book_reviewers
        BookReviewer::create([
            'book_submission_id' => $id,
            'user_id' => $request->user_id,
            'status' => 'PENDING', // Status awal
        ]);

        return redirect()->back()->with('success', 'Reviewer berhasil diundang.');
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

        return redirect()->route('regis-semi.index')
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

        return redirect()->route('regis-semi.index')
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