<?php

namespace App\Http\Controllers\App\Penghargaan;

use App\Http\Controllers\Controller;
use App\Models\BookSubmission;
use App\Models\BookAuthor;
use App\Models\SubmissionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PenghargaanBukuController extends Controller
{
    // ... Method index, create, store, uploadDocs (TIDAK BERUBAH) ...
    public function index()
    {
        $userId = '12e091b8-f227-4a58-8061-dc4a100c60f1';

        $books = BookSubmission::with('authors')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $mappedBooks = $books->map(function ($book) {
            $authorNames = $book->authors->pluck('name')->join(', ');
            return [
                'id' => $book->id,
                'judul' => $book->title,
                'penulis' => $authorNames,
                'penerbit' => $book->publisher,
                'tahun' => $book->publication_year,
                'isbn' => $book->isbn,
                'status' => $this->formatStatus($book->status),
                'kategori' => $this->mapBookTypeToLabel($book->book_type),
                'jumlah_halaman' => $book->total_pages,
            ];
        });

        return Inertia::render('app/penghargaan/buku/page', [
            'pageName' => 'Penghargaan Buku',
            'buku' => $mappedBooks,
        ]);
    }

    public function create()
    {
        return Inertia::render('app/penghargaan/buku/create', [
            'pageName' => 'Formulir Pengajuan Buku'
        ]);
    }

    public function store(Request $request)
    {
        $userId = '12e091b8-f227-4a58-8061-dc4a100c60f1';

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'isbn' => 'required|string|max:20',
            'kategori' => 'required|string',
            'jumlah_halaman' => 'required|integer|min:40',
            'level_penerbit' => 'required|in:NATIONAL,INTERNATIONAL,NATIONAL_ACCREDITED',
        ]);

        DB::beginTransaction();

        try {
            $bookType = $validated['kategori'];

            $book = BookSubmission::create([
                'user_id' => $userId,
                'title' => $validated['judul'],
                'isbn' => $validated['isbn'],
                'publication_year' => $validated['tahun'],
                'publisher' => $validated['penerbit'],
                'publisher_level' => $validated['level_penerbit'],
                'book_type' => $bookType,
                'total_pages' => $validated['jumlah_halaman'],
                // 'file_path' => null, // SUDAH DIHAPUS
                'status' => 'DRAFT',
            ]);

            $authors = explode(',', $validated['penulis']);
            $dosenName = 'Dosen Pengaju (Anda)';

            BookAuthor::create([
                'book_submission_id' => $book->id,
                'user_id' => $userId,
                'name' => $dosenName,
                'role' => 'FIRST_AUTHOR',
                'affiliation' => 'Institut Teknologi Del'
            ]);

            foreach ($authors as $authorName) {
                $cleanName = trim($authorName);
                if (!empty($cleanName)) {
                    BookAuthor::create([
                        'book_submission_id' => $book->id,
                        'name' => $cleanName,
                        'role' => 'CO_AUTHOR',
                        'affiliation' => 'External/Other'
                    ]);
                }
            }

            SubmissionLog::create([
                'book_submission_id' => $book->id,
                'user_id' => $userId,
                'action' => 'CREATE_DRAFT',
                'note' => 'Membuat draft pengajuan buku baru.'
            ]);

            DB::commit();

            return redirect()->route('app.penghargaan.buku.upload', ['id' => $book->id]);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        // Ambil buku beserta relasi penulis
        $book = BookSubmission::with('authors')->findOrFail($id);

        // Opsional: Format data sebelum dikirim ke view agar lebih rapi (seperti di index)
        // Tapi mengirim model langsung juga bisa, asal di frontend dihandle.
        // Di sini saya kirim object book yang sudah di-format sedikit.
        
        return Inertia::render('app/penghargaan/buku/detail', [
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'publisher' => $book->publisher,
                'publication_year' => $book->publication_year,
                'publisher_level' => $book->publisher_level,
                'book_type' => $this->mapBookTypeToLabel($book->book_type), // Reuse helper
                'total_pages' => $book->total_pages,
                'status' => $this->formatStatus($book->status), // Reuse helper
                'drive_link' => $book->drive_link,
                'created_at' => $book->created_at,
                'authors' => $book->authors->map(function($a) {
                    return ['name' => $a->name, 'role' => $a->role];
                }),
            ]
        ]);
    }

    public function uploadDocs($id)
    {
        $book = BookSubmission::findOrFail($id);
        return Inertia::render('app/penghargaan/buku/upload-docs', [
            'pageName' => 'Unggah Dokumen Pendukung',
            'bookId' => $book->id,
            'bookTitle' => $book->title
        ]);
    }

    // [PERBAIKAN] Method ini HANYA MENYIMPAN data link, status TETAP DRAFT
    public function storeUpload(Request $request, $id)
    {
        $userId = '12e091b8-f227-4a58-8061-dc4a100c60f1'; // Ganti Auth::id() nanti

        // Validasi format link (tidak perlu 'required' dulu kalau mau simpan parsial, 
        // tapi kalau formnya mewajibkan semua diisi sekaligus, 'required' oke)
        $request->validate([
            'links' => 'required|array',
            'links.*' => 'nullable|url', // Boleh kosong kalau baru simpan draft
        ]);

        $book = BookSubmission::findOrFail($id);

        // Simpan link sebagai JSON
        // Kita filter yang tidak null agar rapi, atau simpan null juga boleh
        $linksJson = json_encode($request->links);

        $book->update([
            'drive_link' => $linksJson,
            // Status JANGAN diubah jadi SUBMITTED dulu
            'status' => 'DRAFT' 
        ]);

        // Redirect ke halaman DETAIL, bukan Index
        return redirect()->route('app.penghargaan.buku.detail', $book->id)
            ->with('success', 'Dokumen berhasil disimpan sebagai draft. Silakan periksa kembali sebelum dikirim.');
    }

    // [BARU] Method untuk Finalisasi Pengiriman
    public function submit($id)
    {
        $book = BookSubmission::findOrFail($id);
        
        if ($book->status !== 'DRAFT') {
            return back()->with('error', 'Pengajuan sudah dikirim atau diproses.');
        }

        // Validasi Kelengkapan Dokumen di sisi Server (Double Check)
        $links = json_decode($book->drive_link, true);
        
        // Cek apakah array links ada dan isinya minimal 1 (atau 5 sesuai aturan)
        // Asumsi aturan: Wajib 5 link terisi semua
        if (!$links || count(array_filter($links)) < 5) {
            return back()->with('error', 'Dokumen belum lengkap. Harap lengkapi semua link dokumen sebelum mengirim.');
        }

        $book->update([
            'status' => 'SUBMITTED'
        ]);

        SubmissionLog::create([
            'book_submission_id' => $book->id,
            'user_id' => '12e091b8-f227-4a58-8061-dc4a100c60f1', // Ganti Auth
            'action' => 'SUBMIT',
            'note' => 'Pengajuan dikirim final oleh dosen.'
        ]);

        return redirect()->route('app.penghargaan.buku.index')
            ->with('success', 'Pengajuan BERHASIL dikirim ke LPPM.');
    }

    private function formatStatus($status)
    {
        return match ($status) {
            'DRAFT' => 'Draft', // [UBAH] Jangan hardcode "Belum Lengkap" di sini
            'SUBMITTED' => 'Menunggu Verifikasi Staff',
            'VERIFIED_STAFF' => 'Menunggu Review Ketua',
            'APPROVED_CHIEF' => 'Disetujui LPPM',
            'REJECTED' => 'Ditolak/Perlu Revisi',
            'PAID' => 'Selesai (Cair)',
            default => $status,
        };
    }

    private function mapBookTypeToLabel($type)
    {
        return match ($type) {
            'TEACHING' => 'Buku Ajar',
            'REFERENCE' => 'Buku Referensi',
            'MONOGRAPH' => 'Monograf',
            'CHAPTER' => 'Book Chapter',
            default => $type,
        };
    }
}