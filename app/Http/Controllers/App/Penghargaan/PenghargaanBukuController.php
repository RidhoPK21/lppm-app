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
    // Helper untuk mendapatkan User ID (Login atau .env)
    private function getUserId()
    {
        $id = Auth::id() ?? env('DEV_DEFAULT_USER_ID');
        
        if (!$id) {
            abort(403, 'User ID tidak ditemukan. Pastikan Anda login atau set DEV_DEFAULT_USER_ID di file .env');
        }
        
        return $id;
    }

    public function index()
    {
       $userId = Auth::id();

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
       $userId = Auth::id();

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

            // [FIX ENUM] Ubah FIRST_AUTHOR -> FIRST
            BookAuthor::create([
                'book_submission_id' => $book->id,
                'user_id' => $userId,
                'name' => $dosenName,
                'role' => 'FIRST', 
                'affiliation' => 'Institut Teknologi Del'
            ]);

            foreach ($authors as $authorName) {
                $cleanName = trim($authorName);
                if (!empty($cleanName)) {
                    // [FIX ENUM] Ubah CO_AUTHOR -> MEMBER
                    BookAuthor::create([
                        'book_submission_id' => $book->id,
                        'name' => $cleanName,
                        'role' => 'MEMBER', 
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
        $book = BookSubmission::with('authors')->findOrFail($id);

        return Inertia::render('app/penghargaan/buku/detail', [
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'publisher' => $book->publisher,
                'publication_year' => $book->publication_year,
                'publisher_level' => $book->publisher_level,
                'book_type' => $this->mapBookTypeToLabel($book->book_type),
                'total_pages' => $book->total_pages,
                'status' => $this->formatStatus($book->status),
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

    public function storeUpload(Request $request, $id)
    {
       $userId = Auth::id(); // Ganti Auth::id() nanti

        $request->validate([
            'links' => 'required|array|size:5',
            'links.*' => 'required|url',
        ]);

        $book = BookSubmission::findOrFail($id);

        $book->update([
            'drive_link' => json_encode($request->links),
            'status' => 'DRAFT' // Tetap DRAFT agar direview dulu
        ]);

        // Redirect ke detail agar bisa review surat
        return redirect()->route('app.penghargaan.buku.detail', $book->id)
            ->with('success', 'Dokumen disimpan. Silakan review formulir sebelum mengirim.');
    }

    public function submit(Request $request, $id)
    {
        $userId = $this->getUserId();
        $book = BookSubmission::findOrFail($id);
        
        if ($book->status !== 'DRAFT') {
            return back()->with('error', 'Pengajuan sudah dikirim atau diproses.');
        }

        // Validasi Dokumen
        $links = json_decode($book->drive_link, true);
        if (!$links || count(array_filter($links)) < 5) {
            return back()->with('error', 'Dokumen belum lengkap.');
        }

        $book->update([
            'status' => 'SUBMITTED'
        ]);

        SubmissionLog::create([
            'book_submission_id' => $book->id,
            'user_id' => Auth::id(),
            'action' => 'SUBMIT',
            'note' => 'Pengajuan dikirim final oleh dosen.'
        ]);

        return redirect()->route('app.penghargaan.buku.index')
            ->with('success', 'Pengajuan BERHASIL dikirim ke LPPM.');
    }
    
    


    // --- FITUR BARU: GENERATE FORMULIR 8 ---
    public function generateFormulir8(Request $request, $id)
    {
        $book = BookSubmission::with('authors')->findOrFail($id);
        
        // Ambil data user dari request/auth/db fallback
        $user = $request->attributes->get('auth') ?? Auth::user();
        if(!$user) {
             // Fallback terakhir: ambil dari owner buku
             $user = \App\Models\User::find($book->user_id);
        }

        return view('exports.formulir-8', [
            'book' => $book,
            'user' => $user,
            'date' => now()->translatedFormat('d F Y')
        ]);
    }

    // --- FITUR LAMA: GENERATE SURAT PERNYATAAN (Opsional jika masih dipakai) ---
    public function generateStatement(Request $request, $id)
    {
        return $this->generateFormulir8($request, $id); // Redirect logic ke formulir 8 saja
    }

    private function formatStatus($status)
    {
        return match ($status) {
            'DRAFT' => 'Draft',
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