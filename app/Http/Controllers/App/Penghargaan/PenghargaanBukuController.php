<?php

namespace App\Http\Controllers\App\Penghargaan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PenghargaanBukuController extends Controller
{
    /**
     * Menampilkan halaman daftar buku (Index).
     * Berisi Card Informasi dan Tabel Riwayat Pengajuan.
     */
    public function index()
    {
        // DATA DUMMY UNTUK TABEL
        // Sudah disesuaikan dengan kolom-kolom baru (jumlah_halaman, bidang_keilmuan)
        $dummyData = [
            [
                'id' => 1,
                'judul' => 'Rekayasa Perangkat Lunak Modern',
                'penulis' => 'Dr. Arnaldo',
                'penerbit' => 'Andi Publisher',
                'tahun' => '2024',
                'isbn' => '978-1-23-456789-0',
                'status' => 'diajukan',
                'kategori' => 'Buku Referensi',
                'jumlah_halaman' => 150,
                'bidang_keilmuan' => 'Ilmu Komputer'
            ],
            [
                'id' => 2,
                'judul' => 'Algoritma Pemrograman Dasar',
                'penulis' => 'Siti Saragih, M.T.',
                'penerbit' => 'IT Del Press',
                'tahun' => '2023',
                'isbn' => '978-9-87-654321-0',
                'status' => 'disetujui',
                'kategori' => 'Buku Ajar',
                'jumlah_halaman' => 200,
                'bidang_keilmuan' => 'Sistem Informasi'
            ],
            [
                'id' => 3,
                'judul' => 'Sistem Informasi Manajemen',
                'penulis' => 'Budi Santoso',
                'penerbit' => 'Gramedia',
                'tahun' => '2024',
                'isbn' => '978-602-03-1234-5',
                'status' => 'ditolak',
                'kategori' => 'Monograf',
                'jumlah_halaman' => 35, // Contoh < 40 halaman
                'bidang_keilmuan' => 'Manajemen Rekayasa'
            ],
        ];

        return Inertia::render('app/penghargaan/buku/page', [
            'pageName' => 'Penghargaan Buku',
            'buku' => $dummyData,
        ]);
    }

    /**
     * LANGKAH 1: Menampilkan Form Data Buku
     */
    public function create()
    {
        return Inertia::render('app/penghargaan/buku/create', [
            'pageName' => 'Formulir Pengajuan Buku',
        ]);
    }

    /**
     * PROSES LANGKAH 1: Simpan Data Buku Sementara & Redirect ke Upload
     */
    public function store(Request $request)
    {
        // 1. Validasi Input sesuai Pedoman
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun' => 'required|integer|min:1900|max:'.(date('Y')+1),
            'isbn' => 'required|string',
            'kategori' => 'required|string',
            'bidang_keilmuan' => 'required|string',
            'jumlah_halaman' => 'required|integer|min:40', // Validasi min 40 halaman
        ], [
            'jumlah_halaman.min' => 'Sesuai standar UNESCO, buku harus memiliki minimal 40 halaman.',
            'bidang_keilmuan.required' => 'Bidang keilmuan wajib dipilih.',
            'judul.required' => 'Judul buku wajib diisi.',
        ]);

        // 2. Logika Simpan ke Database (Simulasi)
        // $newBook = PenghargaanBuku::create([...]);
        // Kita gunakan ID dummy 999 untuk simulasi
        $newBookId = 999;

        // 3. REDIRECT KE HALAMAN UPLOAD (Bukan kembali ke Index)
        return redirect()->route('app.penghargaan.buku.upload', ['id' => $newBookId])
            ->with('success', 'Data buku berhasil disimpan. Silakan lengkapi dokumen pendukung.');
    }

    /**
     * LANGKAH 2: Menampilkan Halaman Upload Dokumen
     */
    public function uploadDocs($id)
    {
        return Inertia::render('app/penghargaan/buku/upload-docs', [
            'pageName' => 'Unggah Dokumen Pendukung',
            'bookId' => $id
        ]);
    }

    /**
     * PROSES LANGKAH 2: Simpan Dokumen Final & Selesai
     */
    public function storeUpload(Request $request)
    {
        // Validasi Link Drive (Wajib)
        $request->validate([
            'link_drive' => 'required', 
            // Field lain opsional/nullable di validasi jika user boleh mengosongkan
        ], [
            'link_drive.required' => 'Link Google Drive wajib diisi.',
        ]);

        // Logika Update Database untuk menyimpan link dokumen...
        // $book = PenghargaanBuku::find($request->book_id);
        // $book->update([...]);

        // PERUBAHAN PENTING:
        // Return back() agar Frontend bisa menangkap respon 'onSuccess'
        // dan menampilkan SweetAlert terlebih dahulu sebelum redirect manual via JS.
        return back()->with('success', 'Data berhasil disimpan');
    }
}