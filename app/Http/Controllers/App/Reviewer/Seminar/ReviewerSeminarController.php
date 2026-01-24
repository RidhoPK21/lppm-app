<?php

namespace App\Http\Controllers\App\Reviewer\Seminar;

use App\Http\Controllers\Controller;
use App\Models\ProfilDosenModel;
use App\Models\SeminarReviewModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ReviewerSeminarController extends Controller
{
    /**
     * Menampilkan daftar seminar yang MASUK untuk direview
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Safety check (Jika user tidak terdeteksi)
        if (!$user) {
            return redirect()->route('login')->with('error', 'Sesi tidak valid.');
        }

        // 2. Ambil Profil Dosen berdasarkan User Login
        $profil = ProfilDosenModel::where('user_id', $user->id)->first();

        // 3. Auto-Create Profil jika belum ada (Safety Logic)
        if (!$profil) {
            $profil = ProfilDosenModel::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'id' => (string) Str::uuid(),
                    'nidn' => (string) rand(10000000, 99999999), // Dummy NIDN sementara
                    'prodi' => 'Informatika',
                    'jabatan_fungsional' => 'Reviewer (Auto)'
                ]
            );
        }

        // 4. Ambil Daftar Seminar (Real Data)
        // Menggunakan 'with' untuk memuat data relasi seminar, dosen, dan user agar bisa ditampilkan di frontend
        $reviews = SeminarReviewModel::with(['seminar', 'seminar.dosen.user']) 
            ->where('reviewer_id', $profil->id) // Filter hanya untuk reviewer yang login
            ->orderBy('created_at', 'desc')
            ->get();

        // 5. Kirim data ke View (Inertia React/Vue)
        // Data ini akan masuk ke `props.reviews` di file frontend Anda
        return Inertia::render('app/reviewer/seminar/seminar-masuk-page', [
            'pageName' => 'Daftar Seminar Masuk',
            'reviews' => $reviews
        ]);
    }

    /**
     * Mengubah status review (Terima / Tolak)
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:proses,ditolak,selesai',
        ]);

        $review = SeminarReviewModel::with('seminar')->findOrFail($id);
        
        DB::transaction(function () use ($review, $validated) {
            // 1. Update Status Reviewer ini (Misal: dari 'menunggu' jadi 'proses')
            $review->update(['status' => $validated['status']]);

            // 2. CEK APAKAH SEMUA REVIEWER SUDAH MERESPON?
            // Ambil semua review untuk seminar ini yang statusnya masih 'menunggu'
            $pendingReviews = SeminarReviewModel::where('seminar_id', $review->seminar_id)
                                                ->where('status', 'menunggu')
                                                ->count();

            // Jika SISA 0 (artinya semua sudah Terima/Tolak), maka majukan status Seminar
            if ($pendingReviews === 0) {
                // Status 'sedang_direview' akan memicu Dosen pindah ke halaman Hasil Review
                $review->seminar->update(['status_progress' => 'sedang_direview']);
            }
        });

        return back()->with('success', 'Status berhasil diperbarui.');
    }

    public function review($id)
    {
        // --- GANTI DISINI ---
        $review = SeminarReviewModel::with('seminar.dosen.user')->findOrFail($id);
        
        $seminar = $review->seminar;

        return Inertia::render('app/reviewer/seminar/seminar-review-page', [
            'review' => $review,
            'seminar' => $seminar
        ]);
    }

    public function updateReviewContent(Request $request, $id)
    {
        $validated = $request->validate([
            'catatan' => 'required|string',
        ]);

        $review = SeminarReviewModel::with('seminar')->findOrFail($id);
        
        DB::transaction(function () use ($review, $validated) {
            // 1. Update Tabel Review
            $review->update([
                'catatan_review' => $validated['catatan'],
                'status' => 'menunggu_perbaikan', 
            ]);

            // 2. 🔥 UPDATE TABEL SEMINAR (PENTING)
            // Ini yang bikin status di dashboard Dosen berubah warna/teks
            $review->seminar->update([
                'status_progress' => 'menunggu_perbaikan' 
            ]);
        });

        return redirect()->route('reviewer.seminar.masuk')
            ->with('success', 'Review terkirim. Status seminar diperbarui.');
    }

    /**
     * Menangani persetujuan akhir (ACC) paper oleh Reviewer.
     */
    public function accSeminar(Request $request, $reviewId) 
    {
        // Ambil data sebelum update
        $review = SeminarReviewModel::with('seminar')->findOrFail($reviewId);
        $seminar = $review->seminar;

        // 1. Update status review ini menjadi 'selesai'
        // KITA LAKUKAN UPDATE DI SINI SEBELUM CEK COUNTER
        $review->update(['status' => 'selesai', 'keputusan' => 'accept']); 

        // 2. HITUNG REVIEWER AKTIF DAN SELESAI (Setelah update)
        $totalActiveReviewers = SeminarReviewModel::where('seminar_id', $seminar->id)
                                    ->whereIn('status', ['selesai', 'menunggu_perbaikan', 'proses']) 
                                    ->count();
        
        $reviewsSelesai = SeminarReviewModel::where('seminar_id', $seminar->id)
                            ->where('status', 'selesai')
                            ->count();
        
        $isConditionMet = ($totalActiveReviewers > 0 && $totalActiveReviewers === $reviewsSelesai);

        // 3. Update Status Seminar (Jika kondisi terpenuhi)
        if ($isConditionMet) {
            $seminar->update(['status_progress' => 'lolos_review']); 
        }
        
        // 🔥 DEBUGGING KRITIS: Dump semua nilai sebelum redirect 🔥
        // dd("ACC SYNC CHECK COMPLETE!", [
        //     'Status Review yang Diproses' => $review->status,
        //     'Seminar ID' => $seminar->id,
        //     'Status Seminar Sebelum Redirect (Tabel t_seminar)' => $seminar->status_progress,
        //     'Total Reviewers Aktif' => $totalActiveReviewers,
        //     'Reviews Selesai' => $reviewsSelesai,
        //     'Kondisi Lolos (Total === Selesai)' => $isConditionMet ? 'TRUE' : 'FALSE',
        //     'Pesan' => 'Jika Kondisi TRUE, seharusnya status_progress sudah LOLOS_REVIEW.'
        // ]);
        // Logika di bawah dd() tidak akan pernah tercapai.
        
        return redirect()->back()->with('success', 'Seminar berhasil di-ACC.');
    }

    public function downloadForm2($reviewId)
    {
        // 1. Ambil Data Review
        $review = SeminarReviewModel::with(['seminar.dosen.user', 'reviewer.user'])->findOrFail($reviewId);

        // Ambil data profil Reviewer menggunakan ID Profil Dosen (reviewer_id)
        $reviewerProfile = ProfilDosenModel::find($review->reviewer_id); // Metode yang paling benar jika reviewer_id adalah PK m_profil_dosen

        // 🔥 DEBUGGING: Cek apakah data profil sudah terambil dan PRODI-nya ada
        /*
        if (!$reviewerProfile) {
            dd('ERROR: Profil Dosen dengan ID ' . $review->reviewer_id . ' tidak ditemukan.');
        }
        if ($reviewerProfile->program_studi === 'Informatika' || $reviewerProfile->program_studi === 'informatika') {
            dd('SUCCESS: Program Studi terambil: ' . $reviewerProfile->program_studi);
        } else {
            dd('WARNING: Program Studi ditemukan, tapi isinya: ' . $reviewerProfile->program_studi);
        }
        */
        
        // Pastikan review sudah disetujui
        if ($review->status !== 'selesai') {
            return back()->with('error', 'Makalah belum disetujui secara final.');
        }

        // --- Pemrosesan Catatan dan Balasan untuk Halaman 2 (tetap sama) ---
        $catatanList = explode("\n", $review->catatan_review ?? '');
        $balasanList = explode("\n", $review->balasan_revisi ?? '');
        
        $maxRows = max(count($catatanList), count($balasanList));
        $catatanPairs = [];

        for ($i = 0; $i < $maxRows; $i++) {
            $catatan = trim($catatanList[$i] ?? '');
            $balasan = trim($balasanList[$i] ?? '— Tidak ada tanggapan —');
            
            if (!empty($catatan) || !empty($balasan)) {
                $catatanPairs[] = [
                    'catatan' => nl2br(e($catatan)),
                    'balasan' => nl2br(e($balasan)),
                ];
            }
        }
        // --------------------------------------------------------
        
        // 2. Siapkan Data untuk View
        $data = [
            // Data Halaman 1
            'dosen_nama' => $review->seminar->dosen->user->name ?? 'Pemakalah',
            'judul_makalah' => $review->seminar->judul_makalah ?? 'Judul Makalah',
            'reviewer_nama' => $review->reviewer->user->name ?? 'Reviewer',
            
            // 🔥 KRITIKAL: Pengambilan NIDN dan Prodi tanpa fallback, karena sudah dikonfirmasi ada
            'reviewer_nidn' => $reviewerProfile->nidn, 
            'reviewer_prodi' => $reviewerProfile->prodi, 
            
            'revisi_ke' => $review->revisi_ke ?? 0,
            'tanggal_persetujuan' => $review->updated_at, 

            // Data Halaman 2
            'catatan_pairs' => $catatanPairs, 
        ];

        // 3. Generate PDF
        $pdf = Pdf::loadView('pdf.form-2', $data);

        // 4. Download
        return $pdf->download('Formulir-2-Persetujuan-Reviewer-' . ($review->seminar->dosen->user->name ?? 'Dokumen') . '.pdf');
    } 

    /**
     * Menampilkan daftar seminar yang SUDAH DISETUJUI (History)
     */
    public function disetujui()
    {
        $user = Auth::user();
        $profil = ProfilDosenModel::where('user_id', $user->id)->first();

        // Ambil data review yang statusnya 'selesai' milik reviewer ini
        $reviews = SeminarReviewModel::with(['seminar', 'seminar.dosen.user'])
            ->where('reviewer_id', $profil->id)
            ->where('status', 'selesai') // 🔥 Filter Kunci
            ->orderBy('updated_at', 'desc')
            ->get();

        return Inertia::render('app/reviewer/seminar/seminar-disetujui-page', [
            'reviews' => $reviews
        ]);
    }

    /**
     * Download Paper (Draft) yang diupload Dosen
     */
    public function downloadPaper($id)
    {
        // 1. Ambil Data Review berdasarkan ID (karena tombol ada di list review)
        $review = SeminarReviewModel::with('seminar')->findOrFail($id);
        $seminar = $review->seminar;

        // 2. Cek apakah file path ada di database
        if (!$seminar->file_paper_draft) {
            return back()->with('error', 'File paper belum diunggah oleh Dosen.');
        }

        // 3. Cek fisik file di storage
        if (!Storage::disk('public')->exists($seminar->file_paper_draft)) {
            return back()->with('error', 'File fisik tidak ditemukan di server.');
        }

        // 4. Download File
        // Nama file saat didownload: Judul_Makalah.pdf
        $fileName = 'Paper_' . Str::slug($seminar->judul_makalah, '_') . '.pdf';
        
        return Storage::disk('public')->download($seminar->file_paper_draft, $fileName);
    }
}