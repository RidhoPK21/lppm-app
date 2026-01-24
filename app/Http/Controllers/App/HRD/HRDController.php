<?php

namespace App\Http\Controllers\App\HRD;

use App\Http\Controllers\Controller;
use App\Http\Controllers\App\Notifikasi\NotificationController; // Helper Notif Buku
use App\Models\BookSubmission;
use App\Models\SubmissionLog;
use App\Models\SeminarModel;
use App\Models\NotifikasiModel; // Model Notif Seminar
use App\Helper\ToolsHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class HRDController extends Controller
{
    // =========================================================================
    // BAGIAN 1: PENCAIRAN DANA BUKU (Fitur Ridho)
    // =========================================================================

    /**
     * Menampilkan halaman daftar buku yang disetujui LPPM (status APPROVED_CHIEF)
     * Route: hrd.kita.index
     */
    public function index()
    {
        try {
            // ✅ Ambil semua buku dengan status APPROVED_CHIEF SAJA (exclude PAID)
            $books = DB::table('book_submissions')
                ->select(
                    'book_submissions.id',
                    'book_submissions.title as judul',
                    'book_submissions.approved_amount',
                    'book_submissions.created_at as tanggal_pengajuan',
                    'book_submissions.status',
                    'users.name as nama_dosen'
                )
                ->join('users', 'book_submissions.user_id', '=', 'users.id')
                ->where('book_submissions.status', 'APPROVED_CHIEF') // ✅ Hanya APPROVED_CHIEF
                ->orderBy('book_submissions.created_at', 'desc')
                ->get();

            $formattedBooks = $books->map(function ($book) {
                return [
                    'id' => $book->id,
                    'judul' => $book->judul,
                    'nama_dosen' => $book->nama_dosen,
                    'status' => $book->status,
                    'status_label' => 'Disetujui LPPM',
                    'tanggal_pengajuan' => Carbon::parse($book->tanggal_pengajuan)->format('d/m/Y'),
                ];
            });

            Log::info('[HRD Index] Books loaded', [
                'count' => $formattedBooks->count(),
            ]);

            // Return view lama untuk buku
            return Inertia::render('app/home/kita-page', [
                'submissions' => $formattedBooks,
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading HRD page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Inertia::render('app/home/kita-page', [
                'submissions' => [],
            ]);
        }
    }

    /**
     * Proses pencairan dana buku oleh HRD
     * Route: hrd.pencairan
     */
    public function storePencairan(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:book_submissions,id',
            'payment_date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $bookId = $validated['book_id'];
            $paymentDate = $validated['payment_date'];

            // Ambil data buku
            $book = BookSubmission::findOrFail($bookId);

            // Validasi status harus APPROVED_CHIEF
            if ($book->status !== 'APPROVED_CHIEF') {
                DB::rollback();
                return back()->with('error', 'Buku tidak dalam status yang valid untuk pencairan.');
            }

            // ✅ Update status menjadi PAID dan simpan tanggal pencairan
            $book->update([
                'status' => 'PAID',
                'payment_date' => $paymentDate,
                'updated_at' => Carbon::now(),
            ]);

            // Catat log aktivitas
            SubmissionLog::create([
                'book_submission_id' => $bookId,
                'user_id' => Auth::id(),
                'action' => 'PAYMENT_DISBURSED',
                'note' => "Dana penghargaan dicairkan oleh HRD pada tanggal {$paymentDate}",
            ]);

            // 🔥 KIRIM NOTIFIKASI KE DOSEN PENGAJU (Menggunakan NotificationController Helper)
            NotificationController::sendBookPaymentSuccessNotification(
                $bookId,
                $book->title,
                $book->user_id
            );

            DB::commit();

            return redirect()->route('hrd.kita.index')
                ->with('success', 'Pencairan dana berhasil diproses!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('[HRD Payment] Error processing payment', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal memproses pencairan: ' . $e->getMessage());
        }
    }


    // =========================================================================
    // BAGIAN 2: PENERBITAN SURAT TUGAS SEMINAR (Fitur Porman)
    // =========================================================================

    /**
     * Dashboard Seminar HRD (List Request Surat Tugas)
     * Route: hrd.seminar.index
     * NOTE: Nama method diubah dari 'index' menjadi 'indexSeminar' agar tidak bentrok
     */
    public function indexSeminar()
    {
        // Ambil pengajuan yang butuh Surat Tugas (menunggu) atau sudah selesai (riwayat)
        $suratTugasList = SeminarModel::with(['dosen.user'])
            ->whereIn('status_progress', ['menunggu_surat_tugas', 'selesai'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Return view baru untuk seminar
        return Inertia::render('app/hrd/hrd-home-page', [
            'pageName' => 'Penerbitan Surat Tugas',
            'suratTugasList' => $suratTugasList
        ]);
    }

    /**
     * Halaman Upload Surat Tugas
     * Route: hrd.seminar.upload.show
     * NOTE: Nama method diubah dari 'createUpload' menjadi 'showUploadSurat'
     */
    public function showUploadSurat($id)
    {
        $seminar = SeminarModel::with(['dosen.user'])->findOrFail($id);

        // Validasi Status
        if ($seminar->status_progress !== 'menunggu_surat_tugas') {
            return redirect()->route('hrd.seminar.index')
                ->with('error', 'Pengajuan ini tidak dalam status menunggu surat tugas.');
        }

        return Inertia::render('app/hrd/surat-tugas-upload-page', [
            'seminar' => $seminar
        ]);
    }

    /**
     * Proses Simpan Surat Tugas (Finalisasi)
     * Route: hrd.seminar.upload.store
     * NOTE: Nama method diubah dari 'storeUpload' menjadi 'storeUploadSurat'
     */
    public function storeUploadSurat(Request $request, $id)
    {
        $request->validate([
            'file_surat' => 'required|file|mimes:pdf|max:5120', // Maks 5MB
            'nomor_surat' => 'required|string|max:100',
        ], [
            'file_surat.required' => 'File surat tugas wajib diunggah.',
            'nomor_surat.required' => 'Nomor surat wajib diisi.'
        ]);

        DB::beginTransaction();
        try {
            $seminar = SeminarModel::findOrFail($id);

            // 1. Upload File
            $path = $request->file('file_surat')->store('surat-tugas', 'public');

            // 2. Simpan Data ke Metadata
            $meta = $seminar->kewajiban_penelitian ?? [];
            $meta['surat_tugas'] = [
                'file_path' => $path,
                'nomor_surat' => $request->nomor_surat,
                'tanggal_terbit' => now()->toDateString(),
                'diterbitkan_oleh' => auth()->user()->name
            ];

            // 3. Update Status -> SELESAI (Final State)
            $seminar->update([
                'kewajiban_penelitian' => $meta,
                'status_progress' => 'selesai'
            ]);

            // 4. Notifikasi ke Dosen (Manual Create Model karena sistem notif beda)
            if ($seminar->dosen) {
                NotifikasiModel::create([
                    'id' => ToolsHelper::generateId(),
                    'user_id' => $seminar->dosen->user_id,
                    'judul' => 'Surat Tugas Terbit',
                    'pesan' => 'Surat Tugas Anda telah diterbitkan oleh HRD. Proses pengajuan selesai.',
                    'tipe' => 'success',
                    'is_read' => false,
                    // Pastikan route 'dosen.seminar.finish' atau step terakhir sesuai
                    'url_target' => route('dosen.seminar.finish', $seminar->id) 
                ]);
            }

            DB::commit();
            return redirect()->route('hrd.seminar.index')
                ->with('success', 'Surat Tugas berhasil diterbitkan. Alur selesai.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }
}