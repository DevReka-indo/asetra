<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klasifikasi_aset', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique()->comment('Kode klasifikasi, e.g. 101, 102, 201');
            $table->string('nama', 100)->comment('Nama klasifikasi, e.g. Tanah, Gedung, Lemari');
            $table->enum('tipe', ['aset_tetap', 'inventaris'])
                  ->comment('aset_tetap = kode awalan 1xx, inventaris = kode awalan 2xx');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klasifikasi_aset');
    }
};
