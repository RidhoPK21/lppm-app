<?php

namespace App\Http\Controllers\App\Kprodi;

use App\Http\Controllers\Controller;
use App\Models\SeminarModel;
use App\Models\ProfilDosenModel;
use App\Models\NotifikasiModel;
use App\Helper\ToolsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KprodiController extends Controller
{
    // ====================================================
    // 1. DASHBOARD & DAFTAR VERIFIKASI
    // ====================================================
    public function index()
    {
        // Ambil profil Kaprodi yang sedang login
        $user = Auth::user();
        $kaprodiProfil = ProfilDosenModel::where('user_id', $user->id)->first();

        // Safety Check
        if (!$kaprodiProfil) {
            return back()->with('error', 'Profil Kaprodi tidak ditemukan.');
        }

        // Ambil pengajuan yang statusnya "Menunggu Persetujuan Kprodi"
        // DAN Prodi Dosen Pengaju == Prodi Kaprodi
        $verifikasiList = SeminarModel::with(['dosen.user'])
            ->where('status_progress', 'menunggu_persetujuan_kprodi')
            ->whereHas('dosen', function($q) use ($kaprodiProfil) {
                // Filter kesamaan prodi (Opsional: hapus where ini jika ingin melihat semua)
                $q->where('prodi', $kaprodiProfil->prodi);
            })
            ->orderBy('updated_at', 'asc')
            ->get();

        return Inertia::render('app/kprodi/verifikasi-page', [
            'pageName' => 'Verifikasi Pengajuan',
            'verifikasiList' => $verifikasiList
        ]);
    }

    // ====================================================
    // 2. DETAIL PENGAJUAN
    // ====================================================
    public function show($id)
    {
        $seminar = SeminarModel::with(['dosen.user', 'reviews.reviewer.user'])->findOrFail($id);

        return Inertia::render('app/kprodi/detail-verifikasi', [
            'pageName' => 'Detail Verifikasi',
            'seminar' => $seminar
        ]);
    }

    // ====================================================
    // 3. ACTION: SETUJUI (APPROVE)
    // ====================================================
    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $seminar = SeminarModel::findOrFail($id);

            // Update status lanjut ke LPPM
            $seminar->update([
                'status_progress' => 'menunggu_konfirmasi_lppm'
            ]);

            // Notifikasi ke Dosen
            if ($seminar->dosen) {
                NotifikasiModel::create([
                    'id' => ToolsHelper::generateId(),
                    'user_id' => $seminar->dosen->user_id,
                    'judul' => 'Disetujui Kaprodi',
                    'pesan' => 'Pengajuan Anda telah disetujui Kaprodi dan diteruskan ke LPPM.',
                    'tipe' => 'success',
                    'is_read' => false,
                    'url_target' => route('dosen.seminar.index')
                ]);
            }

            // TODO: Notifikasi ke LPPM (Opsional)

            DB::commit();
            return redirect()->route('kprodi.verifikasi.index')
                ->with('success', 'Pengajuan berhasil disetujui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    // ====================================================
    // 4. ACTION: TOLAK (REJECT)
    // ====================================================
    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|min:5'
        ]);

        DB::beginTransaction();
        try {
            $seminar = SeminarModel::findOrFail($id);

            // Kembalikan ke Dosen untuk diperbaiki (Balik ke Step 4 atau Step 1 tergantung kebijakan)
            // Di sini kita kembalikan ke Step 4 (Submit Artefak) agar dia bisa update link
            $seminar->update([
                'status_progress' => 'disetujui', // Status 'disetujui' reviewer memungkinkan dosen akses Step 4 lagi
                // Atau bisa buat status khusus 'revisi_kprodi' jika perlu logic khusus
            ]);
            
            // Simpan catatan penolakan di JSON metadata agar tidak merusak struktur tabel
            $meta = $seminar->kewajiban_penelitian ?? [];
            $meta['catatan_kprodi'] = $request->alasan_penolakan;
            $seminar->kewajiban_penelitian = $meta;
            $seminar->save();

            // Notifikasi ke Dosen
            if ($seminar->dosen) {
                NotifikasiModel::create([
                    'id' => ToolsHelper::generateId(),
                    'user_id' => $seminar->dosen->user_id,
                    'judul' => 'Revisi dari Kaprodi',
                    'pesan' => 'Kaprodi meminta perbaikan: ' . $request->alasan_penolakan,
                    'tipe' => 'error',
                    'is_read' => false,
                    'url_target' => route('dosen.seminar.step4', $seminar->id)
                ]);
            }

            DB::commit();
            return redirect()->route('kprodi.verifikasi.index')
                ->with('success', 'Pengajuan dikembalikan ke Dosen.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak: ' . $e->getMessage());
        }
    }
}