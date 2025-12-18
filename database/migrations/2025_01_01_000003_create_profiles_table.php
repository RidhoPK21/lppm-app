<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Skema harus sesuai dengan ProfileModel.ts.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            // Berdasarkan ProfileModel.ts, ID adalah UUID
            $table->uuid('id')->primary();

            // user_id adalah UUID dan harus unik (1:1 relationship)
            $table->uuid('user_id')->unique();
            
            // Kolom-kolom akademik (semua nullable)
            $table->string('name')->nullable();
            $table->string('nidn')->nullable()->comment('NIDN');
            $table->string('prodi')->nullable()->comment('Prodi');
            $table->string('sinta_id')->nullable()->comment('SintaID');
            $table->string('scopus_id')->nullable()->comment('ScopusID');

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
        Schema::dropIfExists('profiles');
    }
};