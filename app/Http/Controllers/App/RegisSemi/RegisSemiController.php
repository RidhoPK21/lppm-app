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
        $submissions = BookSubmission::with('user')
            ->where('status', '!=', 'DRAFT')
            ->orderBy('created_at', 'desc')
            ->get();

        $mappedData = $submissions->map(function ($item) {
            return [
                'id' => $item->id,
                'judul' => $item->title,
                'nama_dosen' => $item->user->name ?? 'Unknown User',
                'tanggal_pengajuan' => $item->created_at->format('d M Y'),
                'status' => $item->status,
                'status_label' => $this->formatStatusLabel($item->status),
            ];
        });

        return Inertia::render('app/RegisSemi/Index', [
           
            'submissions' => $mappedData,
        ]);
    }

    // Di RegisSemiController.php, tambahkan method ini:

public function indexHRD()
{
    // Ambil hanya buku yang sudah disetujui Ketua LPPM
    $submissions = BookSubmission::with('user')
        ->where('status', 'APPROVED_CHIEF')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'judul' => $item->title,
                'nama_dosen' => $item->user->name ?? 'Unknown User',
                'tanggal_pengajuan' => $item->created_at->format('d M Y'),
                'status' => $item->status,
                'status_label' => $this->formatStatusLabel($item->status),
            ];
        });

    return Inertia::render('app/home/kita-page', [
        
        'submissions' => $submissions,
    ]);
}

    // Action: Staff LPPM Menolak/Minta Revisi (khusus Staff.jsx)
    public function rejectStaff(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string|max:500',
        ]);

        $book = BookSubmission::findOrFail($id);

        DB::transaction(function () use ($book, $request) {
            $book->update([
                'status' => 'REJECTED',
                'reject_note' => $request->note,
            ]);

            // ✅ PERBAIKAN: Pastikan user_id terisi (gunakan Auth::id() atau fallback ke user_id dari book)
            SubmissionLog::create([
                'book_submission_id' => $book->id,
                'user_id' => Auth::id() ?? $book->user_id, // Fallback ke pengaju jika tidak ada auth
                'action' => 'REJECT',
                'note' => $request->note,
            ]);
        });

        return redirect()->route('regis-semi.indexx')
            ->with('success', 'Pengajuan dikembalikan ke Dosen (Staff).');
    }

    public function show($id)
    {
        $book = BookSubmission::with(['authors', 'user'])->findOrFail($id);

        return Inertia::render('app/RegisSemi/Detail', [
           
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'publisher' => $book->publisher,
                'drive_link' => json_decode($book->drive_link),
                'status_label' => $book->status,
                'dosen' => $book->user->name ?? 'Dosen Tidak Ditemukan',
            ]
        ]);
    }

    public function showStaff($id)
    {
        $book = BookSubmission::with(['authors', 'user'])->findOrFail($id);

        return Inertia::render('app/RegisSemi/Staff', [
            
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'publisher' => $book->publisher,
                'drive_link' => json_decode($book->drive_link),
                'status_label' => $book->status,
                'dosen' => $book->user->name ?? 'Dosen Tidak Ditemukan',
            ]
        ]);
    }

    public function invite($id)
    {
        $book = BookSubmission::findOrFail($id);

        $users = User::where('id', '!=', $book->user_id)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($user) use ($book) {
                $isInvited = BookReviewer::where('book_submission_id', $book->id)
                    ->where('user_id', $user->id)
                    ->exists();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'initial' => substr($user->name, 0, 2),
                    'is_invited' => $isInvited
                ];
            });

        return Inertia::render('app/RegisSemi/Invite', [
            'book' => $book,
            'availableReviewers' => $users
        ]);
    }

    public function storeInvite(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        BookReviewer::create([
            'book_submission_id' => $id,
            'user_id' => $request->user_id,
            'status' => 'PENDING',
        ]);

        return redirect()->back()->with('success', 'Reviewer berhasil diundang.');
    }

    // ✅ PERBAIKAN UTAMA: Action Approve - Pastikan user_id terisi
    public function approve(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $book = BookSubmission::findOrFail($id);

        DB::transaction(function () use ($book, $request) {
            $book->update([
                'status' => 'APPROVED_CHIEF',
                'approved_amount' => $request->amount,
            ]);

            // ✅ PERBAIKAN: Pastikan user_id TIDAK NULL
            // Prioritas: 1) User login, 2) Fallback ke pengaju buku
            $userId = Auth::id() ?? $book->user_id;

            SubmissionLog::create([
                'book_submission_id' => $book->id,
                'user_id' => $userId, // Dijamin tidak null
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
            'note' => 'required|string|max:500',
        ]);

        $book = BookSubmission::findOrFail($id);

        DB::transaction(function () use ($book, $request) {
            $book->update([
                'status' => 'REJECTED',
                'reject_note' => $request->note,
            ]);

            // ✅ PERBAIKAN: Pastikan user_id terisi
            SubmissionLog::create([
                'book_submission_id' => $book->id,
                'user_id' => Auth::id() ?? $book->user_id,
                'action' => 'REJECT',
                'note' => $request->note
            ]);
        });

        return redirect()->route('regis-semi.index')
            ->with('success', 'Pengajuan dikembalikan ke Dosen.');
    }

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

    public function indexBukuMasuk()
    {
        $submissions = BookSubmission::with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'judul' => $item->title,
                    'nama_dosen' => $item->user->name ?? '-',
                    'status_label' => $this->formatStatus($item->status),
                    'tanggal_pengajuan' => $item->created_at->format('d M Y, H:i'),
                ];
            });

        return inertia('App/RegisSemi/Indexx', [
            'submissions' => $submissions,
            'pageName' => 'Penghargaan Buku Masuk'
        ]);
    }

    private function formatStatus($status)
    {
        $statusMap = [
            'Draft' => 'Draft',
            'Submitted' => 'Menunggu Verifikasi',
            'Approved' => 'Disetujui (Ke HRD)',
            'Rejected' => 'Ditolak/Revisi',
            'Paid' => 'Selesai (Cair)',
        ];

        return $statusMap[$status] ?? $status;
    }


    public function result()
{
    $submissions = BookSubmission::with('user')
        ->where('status', 'PAID')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'judul' => $item->title,
                'nama_dosen' => $item->user->name ?? '-',
                'status_label' => $this->formatStatus($item->status),
                'tanggal_pengajuan' => $item->created_at->format('d M Y, H:i'),
            ];
        });

    return Inertia::render('App/RegisSemi/Result', [
        'submissions' => $submissions,
        'pageName' => 'Hasil Pengajuan Buku'
    ]);
}

}