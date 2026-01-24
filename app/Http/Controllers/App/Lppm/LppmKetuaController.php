<?php

namespace App\Http\Controllers\App\Lppm;

use App\Http\Controllers\Controller;
use App\Models\SeminarModel;
use App\Models\NotifikasiModel;
use App\Helper\ToolsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\ProfilDosenModel; // Import ini
use Barryvdh\DomPDF\Facade\Pdf; // Import ini

class LppmKetuaController extends Controller
{
    // ====================================================
    // 1. DAFTAR PENGAJUAN MASUK (STATUS: MENUNGGU KONFIRMASI LPPM)
    // ====================================================
    public function index()
    {
        $pengajuanList = SeminarModel::with(['dosen.user'])
            ->where('status_progress', 'menunggu_konfirmasi_lppm')
            ->orderBy('updated_at', 'asc')
            ->get();

        return Inertia::render('app/lppm/ketua/pengajuan-dana-page', [
            'pageName' => 'Persetujuan Dana Seminar',
            'pengajuanList' => $pengajuanList
        ]);
    }

    // ====================================================
    // 2. DETAIL VERIFIKASI
    // ====================================================
    public function show($id)
    {
        // Load relasi dosen dan user agar nama tampil
        $seminar = SeminarModel::with(['dosen.user'])->findOrFail($id);

        return Inertia::render('app/lppm/ketua/detail-konfirmasi-page', [
            'seminar' => $seminar
        ]);
    }

    // ====================================================
    // 3. HALAMAN UPLOAD SURAT (JIKA SETUJU)
    // ====================================================
    public function createUpload($id)
    {
        $seminar = SeminarModel::findOrFail($id);
        return Inertia::render('app/lppm/ketua/pengajuan-dana-upload-page', [
            'seminar' => $seminar
        ]);
    }

    // ====================================================
    // 4. ACTION: APPROVE (SIMPAN SURAT & KIRIM KE KEUANGAN)
    // ====================================================
    public function storeUpload(Request $request, $id)
    {
        $request->validate([
            'file_surat' => 'required|file|mimes:pdf|max:5120', // Maks 5MB
        ], [
            'file_surat.required' => 'Wajib mengunggah Surat Permohonan Dana.',
        ]);

        DB::beginTransaction();
        try {
            $seminar = SeminarModel::findOrFail($id);

            // Upload Surat Pengantar
            $path = $request->file('file_surat')->store('surat-permohonan-dana', 'public');

            // Simpan path surat ke metadata JSON
            $meta = $seminar->kewajiban_penelitian ?? [];
            $meta['file_surat_permohonan'] = $path;

            // UPDATE STATUS: Lanjut ke Keuangan
            $seminar->update([
                'kewajiban_penelitian' => $meta,
                'status_progress' => 'menunggu_pembayaran' 
            ]);

            // Notifikasi ke Dosen
            if ($seminar->dosen) {
                NotifikasiModel::create([
                    'id' => ToolsHelper::generateId(),
                    'user_id' => $seminar->dosen->user_id,
                    'judul' => 'Disetujui LPPM',
                    'pesan' => 'LPPM telah menyetujui pendanaan. Menunggu proses pencairan oleh Keuangan.',
                    'tipe' => 'success',
                    'is_read' => false,
                    'url_target' => route('dosen.seminar.index')
                ]);
            }

            DB::commit();
            return redirect()->route('lppm.ketua.pengajuan-dana')
                ->with('success', 'Surat berhasil diunggah. Pengajuan diteruskan ke Keuangan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // ====================================================
    // 5. HALAMAN TOLAK
    // ====================================================
    public function createReject($id)
    {
        $seminar = SeminarModel::findOrFail($id);
        return Inertia::render('app/lppm/ketua/pengajuan-dana-tolak-page', [
            'seminar' => $seminar
        ]);
    }

    // ====================================================
    // 6. ACTION: TOLAK (KEMBALIKAN KE DOSEN)
    // ====================================================
    public function storeReject(Request $request, $id)
    {
        $request->validate(['alasan' => 'required|string|min:5']);

        DB::beginTransaction();
        try {
            $seminar = SeminarModel::findOrFail($id);
            
            // Kembalikan status ke 'lolos_review' agar Dosen bisa akses Step 4 (Submit Artefak) lagi
            $seminar->update(['status_progress' => 'lolos_review']); 

            // Simpan alasan di metadata
            $meta = $seminar->kewajiban_penelitian ?? [];
            $meta['catatan_lppm'] = $request->alasan;
            $seminar->kewajiban_penelitian = $meta;
            $seminar->save();
            
            // Notifikasi Revisi
            if ($seminar->dosen) {
                NotifikasiModel::create([
                    'id' => ToolsHelper::generateId(),
                    'user_id' => $seminar->dosen->user_id,
                    'judul' => 'Revisi dari LPPM',
                    'pesan' => 'LPPM meminta revisi dokumen: ' . $request->alasan,
                    'tipe' => 'error',
                    'is_read' => false,
                    'url_target' => route('dosen.seminar.step4', $seminar->id)
                ]);
            }

            DB::commit();
            return redirect()->route('lppm.ketua.pengajuan-dana')
                ->with('success', 'Pengajuan dikembalikan ke Dosen.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak: ' . $e->getMessage());
        }
    }

    // ====================================================
    // UPDATE: DOWNLOAD FORM 3 DENGAN NAMA KAPRODI
    // ====================================================
    public function downloadForm3($id)
    {
        $seminar = \App\Models\SeminarModel::with(['dosen.user'])->findOrFail($id);
        $profil = ProfilDosenModel::where('id', $seminar->dosen_profil_id)->first();
        $user = $seminar->dosen->user;

        // --- LOGIKA MENCARI KAPRODI ---
        $kaprodiNama = '...........................................'; // Default jika tidak ketemu
        
        if ($profil && $profil->prodi) {
            // 1. Cari User ID yang punya hak akses 'Kprodi'
            $kprodiUserIds = \App\Models\HakAksesModel::where('akses', 'like', '%Kprodi%')
                ->pluck('user_id');

            // 2. Dari user-user tersebut, cari yang Prodinya SAMA dengan Pengaju
            $kaprodiProfil = ProfilDosenModel::whereIn('user_id', $kprodiUserIds)
                ->where('prodi', $profil->prodi)
                ->first();

            // 3. Ambil Namanya
            if ($kaprodiProfil) {
                $kaprodiUser = \App\Models\User::find($kaprodiProfil->user_id);
                if ($kaprodiUser) {
                    $kaprodiNama = $kaprodiUser->name;
                }
            }
        }

        $data = [
            'seminar' => $seminar,
            'profil' => $profil,
            'user' => $user,
            'tanggal_pengajuan' => $seminar->created_at,
            'estimasi_biaya' => $seminar->kewajiban_penelitian['estimasi_max_dana'] ?? 0,
            'kaprodi_nama' => $kaprodiNama, // 🔥 Kirim data nama ke View
        ];

        $pdf = Pdf::loadView('pdf.form-3', $data);
        return $pdf->download('Form-3-Permohonan-Dana-' . $user->name . '.pdf');
    }
}