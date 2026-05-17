<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('kode_awalan', 10)->unique()->comment('Digit pertama kode kategori, contoh: 1, 2, 3');
            $table->string('nama_jenis', 100)->comment('Nama jenis kategori, contoh: Aset Tetap, Inventaris');
            $table->string('warna_badge', 30)->default('primary')->comment('Warna badge');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_kategori');
    }
};
