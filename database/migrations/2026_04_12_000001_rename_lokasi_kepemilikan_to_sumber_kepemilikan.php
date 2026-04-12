<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename tabel
        Schema::rename('lokasi_kepemilikan', 'sumber_kepemilikan');

        // 2. Rename kolom lama ke kolom baru yang lebih ringkas
        Schema::table('sumber_kepemilikan', function (Blueprint $table) {
            $table->renameColumn('lokasi_kepemilikan_id', 'id');
            $table->renameColumn('kode_lokasi_kepemilikan', 'kode');
            $table->renameColumn('nama_lokasi_kepemilikan', 'nama');
        });
    }

    public function down(): void
    {
        Schema::table('sumber_kepemilikan', function (Blueprint $table) {
            $table->renameColumn('id', 'lokasi_kepemilikan_id');
            $table->renameColumn('kode', 'kode_lokasi_kepemilikan');
            $table->renameColumn('nama', 'nama_lokasi_kepemilikan');
        });

        Schema::rename('sumber_kepemilikan', 'lokasi_kepemilikan');
    }
};
