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
                'file_path' => null,
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

    // [PERUBAHAN UTAMA ADA DI SINI]
    public function storeUpload(Request $request, $id)
    {
        $userId = '12e091b8-f227-4a58-8061-dc4a100c60f1';

        // [UPDATE VALIDASI] Semua 5 link WAJIB diisi
        $request->validate([
            'links' => 'required|array|size:5', // Harus array dengan tepat 5 item
            'links.*' => 'required|url',        // Semua item harus diisi dan format URL
        ], [
            'links.*.required' => 'Link dokumen ini wajib diisi.',
            'links.*.url' => 'Format link harus valid (http/https).',
        ]);

        $book = BookSubmission::findOrFail($id);

        // Karena semua wajib, tidak perlu filter kosong
        $linksJson = json_encode($request->links);

        $book->update([
            'drive_link' => $linksJson,
            'status' => 'SUBMITTED'
        ]);

        SubmissionLog::create([
            'book_submission_id' => $book->id,
            'user_id' => $userId,
            'action' => 'SUBMIT',
            'note' => 'Mengunggah 5 dokumen persyaratan dan mengirim pengajuan.'
        ]);

        return redirect()->route('app.penghargaan.buku.index')
            ->with('success', 'Data berhasil disimpan. Pengajuan Anda sedang diverifikasi.');
    }

    private function formatStatus($status)
    {
        return match ($status) {
            'DRAFT' => 'Draft (Belum Lengkap)',
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