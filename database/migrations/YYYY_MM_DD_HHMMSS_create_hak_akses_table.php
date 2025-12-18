<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('m_hak_akses', function (Blueprint $table) {
            // Berdasarkan HakAksesModel.ts
            $table->uuid('id')->primary();
            
            // user_id
            $table->uuid('user_id');
            
            // akses
            $table->text('akses'); // Tipe data TEXT atau STRING yang cukup besar
            
            $table->timestamps();
            
            // Foreign Key ke users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_hak_akses');
    }
};