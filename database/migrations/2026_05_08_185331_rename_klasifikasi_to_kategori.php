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
        // Hapus tabel kategori_aset
        Schema::dropIfExists('kategori_aset');

        // Rename tabel klasifikasi_aset menjadi kategori_aset
        Schema::rename('klasifikasi_aset', 'kategori_aset');

        // Rename kolom klasifikasi_aset_id menjadi kategori_id di tabel data_aset
        Schema::table('data_aset', function (Blueprint $table) {
            $table->renameColumn('klasifikasi_aset_id', 'kategori_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_aset', function (Blueprint $table) {
            $table->renameColumn('kategori_id', 'klasifikasi_aset_id');
        });

        Schema::rename('kategori_aset', 'klasifikasi_aset');
    }
};
