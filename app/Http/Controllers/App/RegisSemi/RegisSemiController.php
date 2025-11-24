<?php
// File: app/Http/Controllers/App/RegisSemi/RegisSemiController.php

namespace App\Http\Controllers\App\RegisSemi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
// use App\Models\Buku; // Asumsi Anda punya model Buku

class RegisSemiController extends Controller
{
  /**
  * Tampilkan daftar (index) dari buku.
  */
  public function index()
  {
    // $bukuList = Buku::all(); 
    return Inertia::render('App/RegisSemi/Index', [
      // 'buku' => $bukuList,
    ]);
  }
  
  /**
  * Tampilkan detail buku berdasarkan ID.
  */
  public function show($id)
  {
    // $buku = Buku::findOrFail($id);
    return Inertia::render('App/RegisSemi/Detail', [
      'bukuId' => $id, // Mengirim ID untuk contoh
      // 'buku' => $buku, // Mengirim objek buku nyata
    ]);
  }


  public function result($id) // <-- BARU
{
// Di sini Anda mungkin mengambil data hasil penilaian terkait buku dengan $id
// $results = PenilaianDosenModel::where('buku_id', $id)->get();

return Inertia::render('App/RegisSemi/Result', [ // <-- Render ke komponen Result.jsx
'bukuId' => $id,
// 'results' => $results, // Mengirim hasil penilaian nyata
]);
}

  /**
  * Tampilkan halaman undangan dosen untuk penilaian. (showLinkGoogleDrive diubah menjadi invite)
  */
  public function invite($id)
  {
    // Di sini Anda mungkin mengambil data link Google Drive terkait buku dengan $id
    // $dataLink = LinkGoogleDriveModel::where('buku_id', $id)->first();

    return Inertia::render('App/RegisSemi/Invite', [
      'bukuId' => $id, // Mengirim ID buku ke halaman link
      // 'dataLink' => $dataLink, // Mengirim data link nyata
    ]);
  }

  // ... metode lain jika ada (misal: store, update, destroy)
}