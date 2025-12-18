<?php

namespace Database\Factories;

use App\Models\HakAksesModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class HakAksesModelFactory extends Factory
{
    public function modelName()
    {
        return HakAksesModel::class;
    }

    public function definition()
    {
        return [
            'id' => \Illuminate\Support\Str::uuid(),
            'user_id' => \App\Models\User::factory(),
            'akses' => 'DOSEN',
        ];
    }
}