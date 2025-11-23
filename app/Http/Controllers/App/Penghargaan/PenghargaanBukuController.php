<?php

namespace App\Http\Controllers\App\Penghargaan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PenghargaanBukuController extends Controller
{
    public function index()
    {
        // DATA DUMMY
        $dummyData = [
            [
                'id' => 1,
                'judul' => 'Judul Buku 1',
                'penulis' => 'Penulis 1, Penulis 2',
                'penerbit' => 'Penerbit A',
                'tahun' => '2024',
                'isbn' => '978-1-111',
                'status' => 'Belum Disetujui', // Diubah agar merah sesuai gambar
                'kategori' => 'Buku Referensi',
                'jumlah_halaman' => 150,
                'bidang_keilmuan' => 'Ilmu Komputer'
            ],
            [
                'id' => 2,
                'judul' => 'Judul Buku 2',
                'penulis' => 'Penulis 1, Penulis 2',
                'penerbit' => 'Penerbit B',
                'tahun' => '2023',
                'isbn' => '978-2-222',
                'status' => 'Belum Disetujui',
                'kategori' => 'Buku Ajar',
                'jumlah_halaman' => 200,
                'bidang_keilmuan' => 'Sistem Informasi'
            ],
            [
                'id' => 3,
                'judul' => 'Judul Buku 3',
                'penulis' => 'Penulis 1, Penulis 2',
                'penerbit' => 'Penerbit C',
                'tahun' => '2024',
                'isbn' => '978-3-333',
                'status' => 'Belum Disetujui',
                'kategori' => 'Monograf',
                'jumlah_halaman' => 35,
                'bidang_keilmuan' => 'Manajemen Rekayasa'
            ],
             [
                'id' => 4,
                'judul' => 'Judul Buku 4',
                'penulis' => 'Penulis 1, Penulis 2',
                'penerbit' => 'Penerbit D',
                'tahun' => '2024',
                'isbn' => '978-4-444',
                'status' => 'Belum Disetujui',
                'kategori' => 'Monograf',
                'jumlah_halaman' => 45,
                'bidang_keilmuan' => 'Manajemen Rekayasa'
            ],
        ];

        return Inertia::render('app/penghargaan/buku/page', [
            'pageName' => 'Penghargaan Buku',
            'buku' => $dummyData,
        ]);
    }

    // ... Method create, store, uploadDocs, storeUpload SAMA SEPERTI SEBELUMNYA (Tidak berubah)
    public function create() {
        return Inertia::render('app/penghargaan/buku/create', ['pageName' => 'Formulir Pengajuan Buku']);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun' => 'required|integer|min:1900|max:'.(date('Y')+1),
            'isbn' => 'required|string',
            'kategori' => 'required|string',
            'bidang_keilmuan' => 'required|string',
            'jumlah_halaman' => 'required|integer|min:40',
        ], [
            'jumlah_halaman.min' => 'Sesuai standar UNESCO, buku harus memiliki minimal 40 halaman.',
            'bidang_keilmuan.required' => 'Bidang keilmuan wajib dipilih.',
            'judul.required' => 'Judul buku wajib diisi.',
        ]);
        $newBookId = 999;
        return redirect()->route('app.penghargaan.buku.upload', ['id' => $newBookId])
            ->with('success', 'Data buku berhasil disimpan. Silakan lengkapi dokumen pendukung.');
    }

    public function uploadDocs($id) {
        return Inertia::render('app/penghargaan/buku/upload-docs', ['pageName' => 'Unggah Dokumen Pendukung', 'bookId' => $id]);
    }

    public function storeUpload(Request $request) {
        $request->validate(['link_drive' => 'required'], ['link_drive.required' => 'Link Google Drive wajib diisi.']);
        return back()->with('success', 'Data berhasil disimpan');
    }
}