<?php

namespace App\Http\Controllers\App\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\SeminarModel;
use App\Models\NotifikasiModel;
use App\Models\ProfilDosenModel;
use App\Helper\ToolsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KeuanganController extends Controller
{
    // ====================================================
    // 1. DAFTAR PEMBAYARAN (Status: Menunggu Pembayaran)
    // ====================================================
    public function index()
    {
        // Ambil pengajuan yang statusnya relevan dengan Keuangan
        // Termasuk yang sudah cair untuk riwayat
        $seminars = SeminarModel::with(['dosen.user'])
            ->whereIn('status_progress', ['menunggu_pembayaran', 'dana_dicairkan', 'selesai'])
            ->orderBy('updated_at', 'desc') // Yang terbaru di atas
            ->get();

        return Inertia::render('app/keuangan/pembayaran-page', [
            'pageName' => 'Daftar Pencairan Dana',
            'seminars' => $seminars
        ]);
    }

    // ====================================================
    // 2. DETAIL PEMBAYARAN & DOKUMEN PENDUKUNG
    // ====================================================
    public function show($id)
    {
        $seminar = SeminarModel::with(['dosen.user'])->findOrFail($id);
        
        return Inertia::render('app/keuangan/pembayaran-detail-page', [
            'seminar' => $seminar
        ]);
    }

    // ====================================================
    // 3. HALAMAN UPLOAD BUKTI TRANSFER
    // ====================================================
    public function createUpload($id)
    {
        $seminar = SeminarModel::findOrFail($id);
        
        // Validasi: Hanya bisa upload jika status menunggu_pembayaran
        if ($seminar->status_progress !== 'menunggu_pembayaran') {
            return redirect()->route('keuangan.pembayaran')
                ->with('error', 'Pengajuan ini tidak dalam status menunggu pembayaran.');
        }

        return Inertia::render('app/keuangan/pembayaran-upload-page', [
            'seminar' => $seminar
        ]);
    }

    // ====================================================
    // 4. PROSES SIMPAN BUKTI TRANSFER (FINALISASI)
    // ====================================================
    public function storeUpload(Request $request, $id)
    {
        $request->validate([
            'bukti_transfer' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // Maks 5MB
            'jumlah_cair' => 'required|numeric|min:0',
            'tanggal_cair' => 'required|date',
        ], [
            'bukti_transfer.required' => 'Wajib mengunggah file bukti transfer.',
            'jumlah_cair.required' => 'Nominal pencairan wajib diisi.'
        ]);

        DB::beginTransaction();
        try {
            $seminar = SeminarModel::findOrFail($id);

            // 1. Upload File Bukti
            $path = $request->file('bukti_transfer')->store('bukti-transfer', 'public');

            // 2. Simpan Data Pembayaran
            // Kita simpan di kolom JSON metadata seminar agar praktis dan terpusat
            // (Alternatif: Gunakan tabel t_pembayaran jika migrasi sudah siap)
            $meta = $seminar->kewajiban_penelitian ?? [];
            $meta['pembayaran'] = [
                'file_bukti' => $path,
                'jumlah_cair' => $request->jumlah_cair,
                'tanggal_cair' => $request->tanggal_cair,
                'diproses_oleh' => auth()->user()->name
            ];
            
            // 3. Update Status -> DANA_DICAIRKAN
            // Status ini akan memicu Step 6 di Dashboard Dosen (Mode Seminar/Surat Izin)
            $seminar->update([
                'kewajiban_penelitian' => $meta,
                'status_progress' => 'dana_dicairkan' 
            ]);

            // 4. Notifikasi ke Dosen
            if ($seminar->dosen) {
                NotifikasiModel::create([
                    'id' => ToolsHelper::generateId(),
                    'user_id' => $seminar->dosen->user_id,
                    'judul' => 'Dana Telah Dicairkan',
                    'pesan' => 'Dana seminar sebesar Rp ' . number_format($request->jumlah_cair) . ' telah ditransfer. Silakan cek Step 5.',
                    'tipe' => 'success',
                    'is_read' => false,
                    'url_target' => route('dosen.seminar.step5', $seminar->id) // Arahkan dosen ke halaman konfirmasi terima
                ]);
            }

            DB::commit();
            return redirect()->route('keuangan.pembayaran')
                ->with('success', 'Pencairan berhasil diproses. Status diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }
}