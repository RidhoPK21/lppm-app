<?php

namespace App\Livewire\HakAkses;

use App\Helper\ConstHelper;
use App\Helper\ToolsHelper;
use App\Http\Api\UserApi;
use App\Models\HakAksesModel;
use Livewire\Component;

class HakAksesLivewire extends Component
{
    // Attributes
    public $auth;

    public $isEditor = false;

    public $optionRoles = [];

    public $search;

    public $searchPengguna;

    // Attributes untuk form tambah/edit/hapus
    public $dataId;

    public $dataUserId;

    public $dataHakAkses = [];

    public $dataKonfirmasi;

    public $infoDeleteMessage;

    // Fungsi untuk cek akses
    private function checkAkses()
    {
        if (!$this->isEditor) {
            abort(403, 'Unauthorized action.');
        }

        $this->resetErrorBag();
    }

    // Fungsi yang dijalankan saat komponen di-mount
    public $breadcrumbs = [];

    public function mount($auth = null, $searchPengguna = null)
    {
        $this->auth = $auth ?: request()->auth;
        $this->optionRoles = ConstHelper::getOptionRoles();
        $this->searchPengguna = $searchPengguna;

        $this->breadcrumbs = [
            ['label' => 'Home', 'url' => route('app.beranda')],
            ['label' => 'Hak Akses', 'url' => '#'],
        ];
    }

    // Fungsi yang dijalankan saat render
    public function render()
    {
        // Ambil daftar akses

        $aksesList = HakAksesModel::get();

        $response = UserApi::postReqUsersByIds(
            ToolsHelper::getAuthToken(),
            $aksesList->pluck('user_id')->unique()->toArray(),
        );

        $usersList = [];
        if ($response && isset($response->data->users)) {
            $usersList = collect($response->data->users)->map(function ($user) {
                return (object) $user;
            })->all();
        }

        foreach ($aksesList as $akses) {
            $akses->user = collect($usersList)->firstWhere('id', $akses->user_id);

            $data_akses = explode(',', $akses->akses);
            sort($data_akses);
            $akses->data_akses = $data_akses;
        }

        if ($this->search) {
            $aksesList = $aksesList->filter(function ($item) {
                $user = $item->user;
                if (!$user) {
                    return false;
                }

                return str_contains(strtolower($user->name), strtolower($this->search)) ||
                    str_contains(strtolower($user->username), strtolower($this->search));
            });
        }

        // Urutkan akses list berdasarkan nama pengguna
        $aksesList = $aksesList->sortBy(function ($item) {
            $user = $item->user;

            return $user ? strtolower($user->name) : '';
        })->values();

        // Peancarian data pengguna
        $searchPenggunaList = [];
        if ($this->searchPengguna) {
            $authToken = ToolsHelper::getAuthToken();
            $result = UserApi::getUsers($authToken, search: $this->searchPengguna, limit: 5, alias: '');
            if (isset($result->data->users)) {
                $searchPenggunaList = collect($result->data->users)->map(function ($user) {
                    return (object) $user;
                })->all();
            }
        }

        // cek akses
        $this->isEditor = $this->auth ? in_array('Admin', $this->auth->akses) || in_array('Admin', $this->auth->roles) : false;

        $data = [
            'aksesList' => $aksesList,
            'searchPenggunaList' => $searchPenggunaList,
        ];

        return view('features.hak-akses.hak-akses-livewire', $data);
    }

    // Fungsi kelola data
    public $infoDialogTitle;

    public function setUserId($userId)
    {
        $this->dataUserId = $userId;
    }

    public function prepareChange($id)
    {
        $targetAkses = !$id ? null : HakAksesModel::find($id);
        if (!$targetAkses) {
            $this->infoDialogTitle = 'Tambah Hak Akses';
            $this->reset([
                'dataId',
                'dataUserId',
                'dataHakAkses',
            ]);
        } else {
            $this->infoDialogTitle = 'Edit Hak Akses';
            $this->dataId = $targetAkses->id;
            $this->dataUserId = $targetAkses->user_id;
            $this->dataHakAkses = explode(',', $targetAkses->akses);
        }

        $this->dispatch('showDialog', id: 'dialog-change');
    }

    public function onChange()
    {
        $this->checkAkses();

        if (!$this->dataId) {
            // Validasi input
            $this->validate([
                'dataUserId' => 'required',
                'dataHakAkses' => 'required|array',
            ]);

            // Hapus akses lama
            HakAksesModel::where('user_id', $this->dataUserId)->delete();

            // Simpan hak akses baru
            HakAksesModel::create([
                'id' => ToolsHelper::generateId(),
                'user_id' => $this->dataUserId,
                'akses' => implode(',', $this->dataHakAkses),
            ]);

            $this->dispatch('showToastSuccess', message: 'Hak akses berhasil ditambahkan ke pengguna.');
        } else {
            // Validasi input
            $this->validate([
                'dataHakAkses' => 'required|array',
            ]);

            // Update hak akses
            $akses = HakAksesModel::where('id', $this->dataId)->first();
            $akses->akses = implode(',', $this->dataHakAkses);
            $akses->save();

            $this->dispatch('showToastSuccess', message: 'Hak akses berhasil diperbarui ke pengguna.');
        }

        // Reset input
        $this->reset([
            'dataId',
            'dataUserId',
            'dataHakAkses',
        ]);

        $this->dispatch('closeDialog', id: 'dialog-change');
    }

    // Fungsi sebelum hapus data
    public function prepareDelete($id)
    {
        $targetAkses = HakAksesModel::find($id);
        if (!$targetAkses) {
            return;
        }

        $response = UserApi::getUserById(
            ToolsHelper::getAuthToken(),
            $targetAkses->user_id,
        );
        $name = isset($response->data->user) ? $response->data->user->name : '-';

        $this->dataId = $targetAkses->id;
        $this->infoDeleteMessage = "Apakah Anda yakin ingin menghapus hak akses untuk $name dengan ID <br/><strong>{$this->dataId}</strong> ?";

        $this->dispatch('showDialog', id: 'dialog-delete');
    }

    // Fungsi yang menangani aksi hapus data
    public function onDelete()
    {
        $this->checkAkses();

        // Konfirmasi penghapusan
        if ($this->dataKonfirmasi !== $this->dataId) {
            $this->addError('dataKonfirmasi', 'Konfirmasi penghapusan ID tidak sesuai.');
            return;
        }

        HakAksesModel::destroy($this->dataId);

        $this->reset([
            'dataId',
            'dataKonfirmasi',
            'infoDeleteMessage',
        ]);

        $this->dispatch('showToastSuccess', message: 'Hak akses berhasil dihapus dari pengguna.');

        $this->dispatch('closeDialog', id: 'dialog-delete');
    }
}
