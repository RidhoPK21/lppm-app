<?php

namespace App\Http\Controllers\App\Home;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController
{
    public function index(Request $request)
    {
        // Ambil data user yang sudah disimpan di middleware
        $user = $request->attributes->get('auth');

        if (!$user) {
            return Inertia::render('App/home/profile', [
                'user' => null,
                'error' => 'Data pengguna tidak ditemukan.'
            ]);
        }

       return Inertia::render('App/home/profile', [
    'user' => [
        'id'        => $user->id,
        'name'      => $user->name,
        'email'     => $user->email,
        'username'  => $user->username ?? null,
        'photo'     => $user->photo ?? null,
        'NIDN'      => $user->NIDN ?? null,
        'ProgramStudi' => $user->ProgramStudi ?? null,
        'SintaID'   => $user->SintaID ?? null,
        'ScopusID'  => $user->ScopusID ?? null,
    ],
]);

    }
}
