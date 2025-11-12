<?php

namespace App\Livewire\Beranda;

use Livewire\Component;

class BerandaLivewire extends Component
{
    // Attributes
    public $auth;

    // Fungsi yang dijalankan saat komponen di-mount
    public function mount()
    {
        $this->auth = request()->auth;
    }

    // Fungsi yang dijalankan saat render
    public function render()
    {
        return view('features.beranda.beranda-livewire');
    }
}
