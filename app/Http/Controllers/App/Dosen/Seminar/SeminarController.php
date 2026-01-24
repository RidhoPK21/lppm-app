<?php

namespace App\Http\Controllers\App\Dosen\Seminar;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seminar\StoreSeminarDataRequest;
use App\Models\SeminarModel;
use App\Models\SeminarPenulisModel; // Pastikan Model Penulis diimport
use App\Models\ProfilDosenModel;
use App\Models\HakAksesModel;
use App\Models\User;
use App\Models\SeminarReviewModel; // <--- Tambahkan ini
use App\Models\NotifikasiModel;
use App\Helper\ToolsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;



class SeminarController extends Controller
{
    // ====================================================
    // 1. DAFTAR SEMINAR (INDEX) - DENGAN SELF-HEALING OPTIMAL
    // ====================================================
    public function index(Request $request)
    {
        $profil = ProfilDosenModel::where('user_id', Auth::id())->first();
        $search = $request->input('search');
        $status = $request->input('status');

        $seminars = [];
        if ($profil) {
            // 1. Ambil Data Seminar (Query Awal)
            $seminars = SeminarModel::where('dosen_profil_id', $profil->id)
                ->with('reviews') // Load relasi review
                ->orderBy('created_at', 'desc')
                ->get();

            // 2. 🔥 SELF-HEALING: Perbaiki status otomatis SEBELUM filter
            foreach ($seminars as $seminar) {
                $this->syncSeminarStatus($seminar);
            }

            // 3. Filter Manual (Karena data sudah di-fetch dan di-update)
            // Kita filter collection hasil get(), bukan query builder lagi
            // Ini memastikan status yang difilter adalah status TERBARU setelah sync
            if ($search) {
                $seminars = $seminars->filter(function ($item) use ($search) {
                    return stripos($item->judul_makalah, $search) !== false 
                        || stripos($item->nama_forum, $search) !== false;
                });
            }

            if ($status) {
                $seminars = $seminars->where('status_progress', $status);
            }
            
            // Re-index keys agar array urut (Inertia kadang butuh ini)
            $seminars = $seminars->values();
        }

        return Inertia::render('app/dosen/seminar/seminar-page', [
            'pageName' => 'Daftar Seminar Saya',
            'seminars' => $seminars,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }


    /**
     * Helper: Sinkronisasi Status (Fix Reviewer Hantu)
     * Memastikan jika Reviewer ACC, status seminar langsung 'lolos_review'
     */
    /**
     * Helper: Sinkronisasi Status (Fix Reviewer Hantu)
     */
    private function syncSeminarStatus($seminar)
    {
        // Skip jika masih draft/menunggu
        if ($seminar->status_progress === 'draft' || $seminar->reviews->isEmpty()) return;

        $reviews = $seminar->reviews;
        $total = $reviews->count();
        $selesai = $reviews->where('status', 'selesai')->count();
        $revisi = $reviews->whereIn('status', ['menunggu_perbaikan', 'ditolak'])->count();
        
        // LOGIKA PERUBAHAN STATUS OTOMATIS
        $newStatus = null;

        // 1. Jika SEMUA Reviewer ACC -> Status 'lolos_review'
        if ($total > 0 && $selesai === $total) {
            $newStatus = 'lolos_review';
        }
        // 2. Jika ADA yang minta Revisi -> Status 'menunggu_perbaikan'
        elseif ($revisi > 0 && $seminar->status_progress !== 'revisi_masuk') {
            $newStatus = 'menunggu_perbaikan';
        }

        // UPDATE DATABASE & OBJECT MEMORI
        if ($newStatus && $seminar->status_progress !== $newStatus) {
            // Jangan update mundur jika status sudah tahap lanjut
            $tahapLanjut = [
                'menunggu_persetujuan_kprodi', 
                'menunggu_konfirmasi_lppm', 
                'menunggu_pembayaran', 
                'dana_dicairkan',
                'menunggu_surat_tugas', // 🔥 TAMBAHAN PENTING: Jangan reset status ini!
                'selesai',
            ];

            if (!in_array($seminar->status_progress, $tahapLanjut)) {
                SeminarModel::where('id', $seminar->id)->update(['status_progress' => $newStatus]);
                $seminar->status_progress = $newStatus; 
            }
        }
    }
    
    // ====================================================
    // 2. REGISTRASI AWAL (CREATE)
    // ====================================================
      public function create()
{
    // 1. Ambil Profil Dosen (Gunakan user_id dari Auth)
    $profil = ProfilDosenModel::where('user_id', Auth::id())->first();

    // 2. [FIX] Validasi Ketat: Cek 4 Atribut Wajib
    if (!$profil || 
        empty($profil->nidn) || 
        empty($profil->prodi) || 
        empty($profil->sinta_id) || 
        empty($profil->scopus_id)
    ) {
        return redirect()->route('profile.index')
            ->with('error', 'Mohon lengkapi Profil (NIDN, Prodi, Sinta ID, dan Scopus ID) terlebih dahulu sebelum mengajukan seminar.');
    }

    return Inertia::render('app/dosen/seminar/registrasi-awal', [
        'pageName' => 'Registrasi Pengajuan Dana',
    ]);
}

      // ====================================================
      // 3. SIMPAN DATA AWAL (STORE)
      // ====================================================
      
      public function store(Request $request)
    {
        // 1. Validasi Input Lengkap
        $request->validate([
            'judul_makalah' => 'required|string|max:255',
            'penulis' => 'nullable|array',
            'penulis.*.nama' => 'required_with:penulis|string',
            
            // Input Logika Pedoman
            'kategori_luaran' => 'required|string',
            'jumlah_penulis' => 'required',
            'tipe_penulis' => 'required|string', 
            'urutan_penulis' => 'required',
            
            'kewajiban' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $profil = ProfilDosenModel::where('user_id', Auth::id())->firstOrFail();

            // ====================================================
            // LOGIKA HITUNG PAGU FINAL (100% dari Batas Maksimal Registrasi Seminar)
            // ====================================================
            $maxDanaFinal = 0; // Pagu final registrasi
            $kategoriLuaran = $request->kategori_luaran;

            // TAHAP 1: Menentukan Pagu Dasar Registrasi (Bab 3.1 Poin 1)
            // Semua seminar yang memenuhi syarat mendapatkan 100% dari pagu sesuai indeksasi.
            if (str_contains($kategoriLuaran, 'Terindeks')) {
                // Asumsi: Jurnal Internasional Terindeks = Scopus dan Scimagojr 
                $maxDanaFinal = 10000000; // Rp 10.000.000 (Pagu C)
            } elseif (str_contains($kategoriLuaran, 'Internasional') || str_contains($kategoriLuaran, 'Terakreditasi')) {
                // Asumsi: Prosiding Internasional (Terindeks Salah Satu Scopus/WoS) 
                $maxDanaFinal = 5000000; // Rp 5.000.000 (Pagu B)
            } else {
                // Nasional / Prosiding Nasional (Tidak Terindeks) 
                $maxDanaFinal = 1500000; // Rp 1.500.000 (Pagu A)
            }

            // TAHAP 2: Cek Kelayakan Dasar (Wajib menghadiri seminar dan presentasi) 
            // Posisi penulis TIDAK MEMPENGARUHI BESARAN DANA, hanya kelayakan menghadiri.
            
            // Contoh Kelayakan Dasar: Dosen harus tercantum sebagai penulis (asumsi selalu terpenuhi)
            // Jika Dosen mengisi sebagai Co-Author, pagu tetap 100% dari Pagu Dasar.
            
            // Jika Dosen memilih status yang tidak logis (misal urutan > jumlah penulis), 
            // kita bisa menetapkan pagu 0, namun kita anggap validasi di Frontend cukup.
            
            // Pagu Final adalah 100% dari Pagu Dasar Registrasi
            // ====================================================

            // 3. Simpan Data Seminar (Draft)
            $seminar = SeminarModel::create([
                'id' => ToolsHelper::generateId(),
                'dosen_profil_id' => $profil->id,
                'judul_makalah' => $request->judul_makalah,
                
                // Simpan Metadata & Hasil Hitungan ke JSON
                'kewajiban_penelitian' => [
                    'checklist' => $request->kewajiban,
                    'meta_penulis' => [
                        'jumlah' => $request->jumlah_penulis,
                        'tipe' => $request->tipe_penulis,
                        'urutan' => $request->urutan_penulis,
                    ],
                    'kategori_luaran' => $request->kategori_luaran,
                    // Kita simpan Pagu Final 100%
                    'estimasi_max_dana' => $maxDanaFinal 
                ],
                
                // Default Value
                'nama_forum' => '-', 
                'institusi_penyelenggara' => '-',
                'tanggal_mulai' => now(), 
                'tanggal_selesai' => now(),
                'tempat_pelaksanaan' => '-',
                'biaya_registrasi' => 0,
                'status_progress' => 'draft',
            ]);

            // 4. Simpan Data Penulis (Looping)
            if ($request->has('penulis') && is_array($request->penulis)) {
                foreach ($request->penulis as $p) {
                    if (!empty($p['nama'])) {
                        SeminarPenulisModel::create([
                            'id' => ToolsHelper::generateId(),
                            'seminar_id' => $seminar->id,
                            'nama' => $p['nama'],
                            'tipe_penulis' => 'Anggota'
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('dosen.seminar.step1', ['id' => $seminar->id])
                ->with('success', 'Registrasi berhasil. Pagu dana disetujui: Rp ' . number_format($maxDanaFinal));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage() . ' Line: ' . $e->getLine());
        }
    }

    // ====================================================
    // 4. STEP 1: LENGKAPI DATA (EDIT)
    // ====================================================
    public function editStep1($id)
    {
        $seminar = $this->getSeminar($id);
        
        // Load relasi penulis agar muncul di form edit jika perlu
        $seminar->load('penulis'); 

        return Inertia::render('app/dosen/seminar/step-1-data', [
            'pageName' => 'Lengkapi Data Seminar',
            'seminar' => $seminar 
        ]);
    }

    public function updateStep1(StoreSeminarDataRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $seminar = $this->getSeminar($id);
            
            // 1. Update Data Utama Seminar (Judul, Forum, Waktu, Biaya, dll)
            $seminar->update($request->validated());

            // 2. Update Data Penulis (Opsional, jika form Step 1 mengirim data penulis juga)
            // Karena view Step 1 Anda sekarang HANYA input detail seminar, bagian ini opsional.
            // Namun saya biarkan agar robust jika nanti Anda ingin edit penulis di step 1.
            /* if ($request->has('penulis')) {
                SeminarPenulisModel::where('seminar_id', $seminar->id)->delete();
                foreach ($request->penulis as $p) {
                    if (!empty($p['nama'])) {
                        SeminarPenulisModel::create([
                            'id' => ToolsHelper::generateId(),
                            'seminar_id' => $seminar->id,
                            'nama' => $p['nama'],
                            'tipe_penulis' => 'Anggota'
                        ]);
                    }
                }
            }
            */

            DB::commit();

            // Redirect ke Step 2 (Upload Paper)
            return redirect()->route('dosen.seminar.step2', ['id' => $seminar->id])
                ->with('success', 'Data seminar tersimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    // ====================================================
    // 5. STEP 2: UPLOAD PAPER
    // ====================================================
    public function createStep2($id)
    {
        $seminar = $this->getSeminar($id);
        
        // Ambil reviewer dari hak akses dan profil dosen
        $reviewers = HakAksesModel::where('akses', 'like', '%Reviewer%')
                        ->get()
                        ->map(function($hak) {
                            $user = User::find($hak->user_id);
                            $profilReviewer = ProfilDosenModel::where('user_id', $hak->user_id)->first();
                            
                            // Hanya tampilkan user yang punya profil dosen (NIDN)
                            if ($user && $profilReviewer) {
                                return [
                                    'value' => $profilReviewer->id, // ID Profil Dosen (bukan User ID)
                                    'label' => $user->name
                                ];
                            }
                            return null;
                        })->filter()->values();

        return Inertia::render('app/dosen/seminar/step-2-paper', [
            'pageName' => 'Upload Paper',
            'seminar' => $seminar,
            'reviewers' => $reviewers
        ]);
    }

    public function storeStep2(Request $request, $id)
    {
        // 1. Validasi Array
        $request->validate([
            'file_draft' => 'required|file|mimes:pdf|max:15360',
            'reviewer_ids' => 'required|array|min:1', // Wajib array minimal 1
            'reviewer_ids.*' => 'required|exists:m_profil_dosen,id|distinct', // Cek tiap item & tidak boleh duplikat
        ], [
            'reviewer_ids.*.required' => 'Wajib memilih dosen reviewer.',
            'reviewer_ids.*.exists' => 'Data dosen tidak valid.',
            'reviewer_ids.*.distinct' => 'Dosen reviewer tidak boleh sama.'
        ]);

        $seminar = $this->getSeminar($id);

        DB::beginTransaction();
        try {
            // 2. Upload File
            if ($request->hasFile('file_draft')) {
                $seminar->file_paper_draft = $request->file('file_draft')->store('papers/draft', 'public');
            }
            
            // 3. Hapus Reviewer Lama (Jika ada, agar tidak duplikat saat re-upload)
            SeminarReviewModel::where('seminar_id', $seminar->id)->delete();

            // 4. Simpan Banyak Reviewer (Looping)
            foreach ($request->reviewer_ids as $revId) {
                // Simpan ke tabel Review
                SeminarReviewModel::create([
                    'id' => ToolsHelper::generateId(),
                    'seminar_id' => $seminar->id,
                    'reviewer_id' => $revId,
                    'status' => 'menunggu'
                ]);

                // Kirim Notifikasi ke Masing-Masing Reviewer
                $profilReviewer = ProfilDosenModel::find($revId);
                if ($profilReviewer) {
                    NotifikasiModel::create([
                        'id' => ToolsHelper::generateId(),
                        'user_id' => $profilReviewer->user_id,
                        'judul' => 'Tugas Review Baru',
                        'pesan' => 'Anda ditunjuk sebagai reviewer untuk makalah: ' . $seminar->judul_makalah,
                        'tipe' => 'info',
                        'is_read' => false,
                        'url_target' => route('reviewer.seminar.review', ['id' => $seminar->id])
                    ]);
                }
            }

            // 5. Update Status Seminar
            $seminar->status_progress = 'menunggu_reviewer';
            $seminar->save();

            DB::commit();
            // Kita harus menyertakan ID seminar agar Laravel tahu tujuannya
            return redirect()->route('dosen.seminar.step3', ['id' => $seminar->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal upload: ' . $e->getMessage());
        }
    }

    // ====================================================
    // 6. HAPUS SEMINAR (DELETE)
    // ====================================================
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $seminar = $this->getSeminar($id);

            // Hanya izinkan hapus jika statusnya DRAFT
            if ($seminar->status_progress !== 'draft') {
                DB::rollBack();
                // 🔥 Gunakan back()->with() untuk flash message Inertia/Laravel
                return back()->with('error', 'Hanya pengajuan berstatus Draft yang dapat dihapus.');
            }

            // Hapus relasi anak yang terkait (Penulis, dll.)
            \App\Models\SeminarPenulisModel::where('seminar_id', $seminar->id)->delete();
            
            $seminar->delete();

            DB::commit();
            // 🔥 Gunakan back()->with() untuk flash message Inertia/Laravel
            return back()->with('success', 'Pengajuan berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus pengajuan. Silakan coba lagi.');
        }
    }


    // ====================================================
    // 🔥 FITUR BARU: HALAMAN PERBAIKAN PAPER (STEP 3)
    // ====================================================
    public function showPerbaikanPage($id)
    {
        // 1. Ambil data Seminar
        $seminar = $this->getSeminar($id);

        // 2. Ambil data Review terkait
        // Kita ambil review yang statusnya 'menunggu_perbaikan' atau ambil yang terbaru
        $review = SeminarReviewModel::where('seminar_id', $seminar->id)->first();

        // Jika tidak ada review, kembalikan (validasi)
        if (!$review) {
            return redirect()->route('dosen.seminar.index')
                ->with('error', 'Belum ada data review untuk seminar ini.');
        }

        return Inertia::render('app/dosen/seminar/step-3-perbaikan', [
            'pageName' => 'Perbaikan Paper',
            'seminar' => $seminar,
            'review' => $review // Dikirim agar Dosen bisa baca catatan_review
        ]);
    }

    // ====================================================
    // FITUR BARU: SUBMIT REVISI (UPDATE)
    // ====================================================
    public function submitPerbaikan(Request $request, $id)
    {
        // 1. Validasi
        $request->validate([
            'file_revisi' => 'required|file|mimes:pdf|max:15360', // Maks 15MB
            'balasan_revisi' => 'nullable|array', // Validasi array respon dosen
        ]);

        DB::beginTransaction();
        try {
            $seminar = $this->getSeminar($id);
            
            // 🔥 PERBAIKAN KRUSIAL: Ambil SEMUA review untuk di-update
            $reviews = SeminarReviewModel::where('seminar_id', $seminar->id)->get();
            
            if ($reviews->isEmpty()) {
                 throw new \Exception("Tidak ditemukan entri review terkait seminar ini.");
            }
            
            // 2. Upload File Baru (Revisi)
            if ($request->hasFile('file_revisi')) {
                // Opsional: Hapus file lama jika mau hemat storage
                // if ($seminar->file_paper_draft) Storage::disk('public')->delete($seminar->file_paper_draft);

                $path = $request->file('file_revisi')->store('papers/draft', 'public');
                
                // Update Path di Tabel Seminar
                $seminar->update([
                    'file_paper_draft' => $path
                ]);
            }

            // 3. Proses Balasan Revisi (Array -> String)
            $balasanText = null;
            if ($request->balasan_revisi) {
                // Menggabungkan array respon dengan separator baris baru
                $balasanText = implode("\n", array_filter($request->balasan_revisi, fn($value) => !is_null($value) && $value !== ''));
            }

            // 4. Update Status dan Kirim Notifikasi untuk SETIAP REVIEWER
            foreach ($reviews as $review) {
                // Hanya update review yang statusnya belum disetujui final
                if ($review->status !== 'selesai') {
                    $review->update([
                        'status' => 'proses', // Kembalikan status ke PROSES (Sedang Ditinjau)
                        'revisi_ke' => ($review->revisi_ke ?? 0) + 1, // Counter naik
                        'balasan_revisi' => $balasanText, // Simpan tanggapan
                    ]);

                    // Kirim Notifikasi Balik ke Reviewer
                    $profilReviewer = ProfilDosenModel::find($review->reviewer_id);
                    if ($profilReviewer) {
                        NotifikasiModel::create([
                            'id' => ToolsHelper::generateId(),
                            'user_id' => $profilReviewer->user_id,
                            'judul' => 'Revisi Masuk',
                            'pesan' => 'Dosen telah mengupload revisi untuk: ' . $seminar->judul_makalah,
                            'tipe' => 'info',
                            'is_read' => false,
                            'url_target' => route('reviewer.seminar.review', ['id' => $review->id])
                        ]);
                    }
                }
            }
            
            // 5. Update Status Progress Seminar (Sekali saja)
            $seminar->update([
                'status_progress' => 'revisi_masuk'
            ]);

            DB::commit();

            // 🔥 PERBAIKAN REDIRECT SERVER-SIDE 🔥
            // Redirect ke halaman detail/hasil review yang benar
            return redirect()->route('dosen.seminar.step3.hasil', $seminar->id) 
                ->with('success', 'Revisi dan tanggapan berhasil dikirim! Menunggu pengecekan ulang oleh reviewer.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim revisi: ' . $e->getMessage());
        }
    }

    // ====================================================
    // 🔥 HANDLER PINTAR UNTUK STEP 3
    // ====================================================
   public function step3Handler($id)
    {
        $seminar = $this->getSeminar($id);

        // 1. 🔥 PENTING: Panggil Sync dulu biar status terbaru ter-load
        // Pastikan method ini ada di controller Anda
        $this->syncSeminarStatus($seminar); 

        switch ($seminar->status_progress) {
            
            // KASUS 1: Masuk ke Halaman HASIL REVIEW (Card List)
            case 'sedang_direview':
            case 'menunggu_perbaikan':
            case 'revisi_masuk':
            case 'disetujui':
            case 'lolos_review': // <--- 🔥 WAJIB ADA INI (Status dari Reviewer ACC)
            case 'selesai':
                // Redirect ke Halaman LIST CARD (Hasil Review)
                return redirect()->route('dosen.seminar.step3.hasil', $id);

            // KASUS 2: Masih Menunggu (Loading)
            case 'menunggu_reviewer':
            default:
                return Inertia::render('app/dosen/seminar/step-3-finalisasi', [
                    'pageName' => 'Menunggu Konfirmasi Reviewer',
                    'seminar' => $seminar,
                    'status_message' => 'Mohon menunggu sampai seluruh Reviewer menerima penugasan.'
                ]);
        }
    }

    public function showHasilReviewPage($id)
    {
        $seminar = $this->getSeminar($id);
        
        // Ambil semua review terkait seminar ini, beserta data dosen reviewernya
        $reviews = SeminarReviewModel::with('reviewer.user') // Relasi ke user reviewer
                    ->where('seminar_id', $id)
                    ->get();

        return Inertia::render('app/dosen/seminar/step-3-hasil-review', [
            'seminar' => $seminar,
            'reviews' => $reviews
        ]);
    }


    // ====================================================
    // HELPER
    // ====================================================
    private function getSeminar($id) {
        $profil = ProfilDosenModel::where('user_id', Auth::id())->firstOrFail();
        return SeminarModel::where('id', $id)->where('dosen_profil_id', $profil->id)->firstOrFail();
    }

    // ====================================================
    // 🔥 DOWNLOAD FORM 1 (HASIL REVIEW)
    // ====================================================
    public function downloadForm1($reviewId)
    {
        // 1. Ambil Data Review berdasarkan ID Review (bukan ID Seminar)
        $review = SeminarReviewModel::with(['seminar.dosen.user', 'reviewer.user'])->findOrFail($reviewId);
        
        // 2. Siapkan Data untuk View
        $data = [
            'dosen_nama' => $review->seminar->dosen->user->name ?? 'Nama Dosen',
            'judul_makalah' => $review->seminar->judul_makalah ?? 'Judul Makalah',
            'catatan_review' => $review->catatan_review,
            'tanggal_review' => $review->updated_at, // Tanggal terakhir diupdate (saat submit review)
            'reviewer_nama' => $review->reviewer->user->name ?? 'Nama Reviewer',
        ];

        // 3. Generate PDF
        $pdf = Pdf::loadView('pdf.form-1', $data);

        // 4. Download
        return $pdf->download('Form-1-Hasil-Review.pdf');
    }

    // ====================================================
    // 7. DOWNLOAD PDF FORM 3
    // ====================================================
    public function downloadForm3($id)
    {
        // Ambil data seminar milik dosen yang sedang login
        $seminar = $this->getSeminar($id);
        
        // Ambil profil dosen & user terkait
        $profil = ProfilDosenModel::where('id', $seminar->dosen_profil_id)->first();
        $user = User::find($profil->user_id);

        // Generate PDF dari view
        $pdf = Pdf::loadView('pdf.form-3', [
            'seminar' => $seminar,
            'profil' => $profil,
            'user' => $user
        ]);

        // Download file
        return $pdf->download('Form-3-Permohonan-Dana-' . $user->name . '.pdf');
    }

    // ====================================================
    // 8. STEP 4: SUBMIT ARTEFAK (PERBAIKAN LOGIC)
    // ====================================================
    public function createStep4($id)
    {
        $seminar = $this->getSeminar($id);

        // 1. 🔥 FORCE SYNC: PENTING! 
        // Perbaiki status dulu sebelum mengecek izin. 
        // Ini memastikan jika Reviewer sudah ACC, status langsung jadi 'lolos_review' detik ini juga.
        $this->syncSeminarStatus($seminar); 

        // 2. Daftar status yang diizinkan masuk
        $allowedStatus = [
            'disetujui', 
            'lolos_review', // Pastikan status ini ada
            'menunggu_persetujuan_kprodi', 
            'menunggu_konfirmasi_lppm',
            'menunggu_pembayaran',
            'selesai',
            'dana_dicairkan',
            'menunggu_surat_tugas'
        ];

        // 3. Cek Izin
        if (!in_array($seminar->status_progress, $allowedStatus)) {
            // Jika ditolak, beri pesan error agar kita tahu kenapa
            return redirect()->route('dosen.seminar.step3', $id)
                ->with('error', 'Status saat ini (' . $seminar->status_progress . ') belum diizinkan masuk ke Step 4.');
        }

        return Inertia::render('app/dosen/seminar/step-4-artefak', [
            'pageName' => 'Submit Artefak',
            'seminar' => $seminar
        ]);
    }

    public function storeStep4(Request $request, $id)
    {
        // 1. Validasi Array Link (Wajib diisi semua atau sebagian)
        $request->validate([
            'artefak_links' => 'required|array',
            'artefak_links.*' => 'nullable|url', // Validasi format URL untuk setiap input
        ]);

        DB::beginTransaction();
        try {
            $seminar = $this->getSeminar($id);

            // 2. Ambil data existing di kolom JSON
            $kewajiban = $seminar->kewajiban_penelitian ?? [];
            
            // 3. Simpan Array Link ke dalam metadata JSON
            // Data akan tersimpan bentuk: {"link_artefak": ["url1", "url2", ...]}
            $kewajiban['link_artefak'] = $request->artefak_links;

            $seminar->update([
                'kewajiban_penelitian' => $kewajiban,
                'status_progress' => 'menunggu_persetujuan_kprodi' 
            ]);

            DB::commit();

            return redirect()->route('dosen.seminar.index')
                ->with('success', 'Semua link artefak berhasil disimpan! Menunggu verifikasi Kaprodi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // ====================================================
    // 9. STEP 5: STATUS PENCAIRAN DANA
    // ====================================================
    public function createStep5($id)
    {
        $seminar = $this->getSeminar($id);

        // Status yang valid untuk masuk halaman ini
        $allowedStatus = [
            'menunggu_pembayaran',
            'dana_dicairkan',
            'selesai'
        ];

        // Jika status belum sampai sini, kembalikan ke Step 4 atau Dashboard
        if (!in_array($seminar->status_progress, $allowedStatus)) {
            // Cek jika masih di tahap verifikasi sebelumnya
            if (in_array($seminar->status_progress, ['menunggu_persetujuan_kprodi', 'menunggu_konfirmasi_lppm'])) {
                 return redirect()->route('dosen.seminar.step4', $id);
            }
            return redirect()->route('dosen.seminar.index');
        }

        return Inertia::render('app/dosen/seminar/step-5-pencairan', [
            'pageName' => 'Status Pencairan',
            'seminar' => $seminar
        ]);
    }

    // ====================================================
    // 10. STEP 6: PILIH MODE SEMINAR (ONLINE / ONSITE)
    // ====================================================
    public function createStep6($id)
    {
        $seminar = $this->getSeminar($id);

        // Hanya boleh masuk jika dana sudah cair
        if ($seminar->status_progress !== 'dana_dicairkan') {
            // Jika sudah selesai, langsung lempar ke halaman finish
            if ($seminar->status_progress === 'selesai') {
                return redirect()->route('dosen.seminar.finish', $id);
            }
            // Jika menunggu surat tugas (Onsite), tampilkan halaman menunggu
            if ($seminar->status_progress === 'menunggu_surat_tugas') {
                 return Inertia::render('app/dosen/seminar/step-6-mode', [
                    'pageName' => 'Menunggu Surat Tugas',
                    'seminar' => $seminar,
                    'isWaiting' => true
                ]);
            }
            return redirect()->route('dosen.seminar.step5', $id);
        }

        return Inertia::render('app/dosen/seminar/step-6-mode', [
            'pageName' => 'Mode Seminar',
            'seminar' => $seminar,
            'isWaiting' => false
        ]);
    }

    public function storeStep6(Request $request, $id)
    {
        $request->validate([
            'mode' => 'required|in:online,onsite'
        ]);

        DB::beginTransaction();
        try {
            $seminar = $this->getSeminar($id);
            
            // Simpan mode ke metadata
            $meta = $seminar->kewajiban_penelitian ?? [];
            $meta['mode_pelaksanaan'] = $request->mode;

            if ($request->mode === 'online') {
                // ONLINE: Langsung Selesai
                $seminar->update([
                    'kewajiban_penelitian' => $meta,
                    'status_progress' => 'selesai'
                ]);
                $msg = 'Seminar Online dipilih. Proses Pengajuan Selesai!';
                $route = 'dosen.seminar.finish';
            } else {
                // ONSITE: Minta Surat Tugas ke HRD
                $seminar->update([
                    'kewajiban_penelitian' => $meta,
                    'status_progress' => 'menunggu_surat_tugas'
                ]);
                $msg = 'Permintaan Surat Tugas dikirim ke HRD. Mohon tunggu.';
                $route = 'dosen.seminar.index'; // Balik ke dashboard dulu
            }

            DB::commit();
            return redirect()->route($route, $id)->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // ====================================================
    // 11. FINISH PAGE (REKAP AKHIR)
    // ====================================================
    public function finish($id)
    {
        $seminar = $this->getSeminar($id);

        if ($seminar->status_progress !== 'selesai') {
            return redirect()->route('dosen.seminar.index');
        }

        return Inertia::render('app/dosen/seminar/step-finish', [
            'pageName' => 'Pengajuan Selesai',
            'seminar' => $seminar
        ]);
    }

    
}