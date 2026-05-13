<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Recreate manual tables in their BARE MINIMUM state.
     */
    public function up(): void
    {
        // 1. lokasi_aset
        if (!Schema::hasTable('lokasi_aset')) {
            Schema::create('lokasi_aset', function (Blueprint $table) {
                $table->id('lokasi_id');
                $table->string('kode_lokasi', 20)->unique();
                $table->string('nama_lokasi', 100);
                $table->string('detail_lokasi', 255)->nullable();
                $table->timestamps();
            });
        }

        // 2. lokasi_kepemilikan
        if (!Schema::hasTable('lokasi_kepemilikan') && !Schema::hasTable('sumber_kepemilikan')) {
            Schema::create('lokasi_kepemilikan', function (Blueprint $table) {
                $table->increments('lokasi_kepemilikan_id');
                $table->string('kode_lokasi_kepemilikan', 20);
                $table->string('nama_lokasi_kepemilikan', 100);
                $table->timestamps();
            });
        }

        // 2b. jenis_aset_umum
        if (!Schema::hasTable('jenis_aset_umum')) {
            Schema::create('jenis_aset_umum', function (Blueprint $table) {
                $table->increments('id');
                $table->string('kode_umum', 10)->unique();
                $table->string('jenis_aset', 100);
                $table->timestamps();
            });
        }

        // 2c. jenis_aset_khusus
        if (!Schema::hasTable('jenis_aset_khusus')) {
            Schema::create('jenis_aset_khusus', function (Blueprint $table) {
                $table->increments('id');
                $table->string('kode_khusus', 10)->unique();
                $table->string('jenis_aset', 100);
                $table->unsignedInteger('jenis_aset_umum_id');
                $table->timestamps();
            });
        }

        // 2d. kategori_aset
        if (!Schema::hasTable('kategori_aset')) {
            Schema::create('kategori_aset', function (Blueprint $table) {
                $table->id('kategori_id');
                $table->string('kode', 10)->unique();
                $table->string('nama', 100);
                $table->timestamps();
            });
        }

        // 3. data_aset
        if (!Schema::hasTable('data_aset')) {
            Schema::create('data_aset', function (Blueprint $table) {
                $table->id();
                $table->string('nama_aset', 150);
                $table->string('nomor_aset', 100)->nullable()->unique();
                
                $table->unsignedInteger('jenis_aset_umum_id')->nullable();
                $table->unsignedInteger('jenis_aset_khusus_id')->nullable();
                $table->unsignedInteger('id_lokasi_kepemilikan')->nullable(); // will be renamed later
                
                $table->text('deskripsi')->nullable();
                $table->string('merek', 100)->nullable();
                $table->integer('tahun_kapitalisasi')->nullable();
                
                $table->unsignedBigInteger('id_divisi')->nullable(); 
                $table->unsignedBigInteger('lokasi_id')->nullable();
                $table->unsignedBigInteger('pic_id')->nullable();
                $table->string('status_kondisi', 50)->default('Baik');
                $table->string('status_aset', 50)->default('Aktif');
                $table->text('keterangan')->nullable();
                
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('data_aset');
        Schema::dropIfExists('lokasi_kepemilikan');
        Schema::dropIfExists('sumber_kepemilikan');
        Schema::dropIfExists('lokasi_aset');
        Schema::dropIfExists('jenis_aset_umum');
        Schema::dropIfExists('jenis_aset_khusus');
        Schema::dropIfExists('kategori_aset');
    }
};
