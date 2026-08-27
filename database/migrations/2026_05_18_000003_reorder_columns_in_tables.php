<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // 1. lokasi_aset: nama_lokasi AFTER lokasi_id
        DB::statement('ALTER TABLE lokasi_aset MODIFY COLUMN nama_lokasi VARCHAR(100) NOT NULL AFTER lokasi_id');

        // 2. jenis_kategori: nama_jenis AFTER id
        DB::statement('ALTER TABLE jenis_kategori MODIFY COLUMN nama_jenis VARCHAR(100) NOT NULL AFTER id');

        // 3. kategori_aset: nama AFTER id
        DB::statement('ALTER TABLE kategori_aset MODIFY COLUMN nama VARCHAR(100) NOT NULL AFTER id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Revert columns to their original order (nama_lokasi AFTER kode_lokasi, nama_jenis AFTER kode_awalan, nama AFTER kode)
        DB::statement('ALTER TABLE lokasi_aset MODIFY COLUMN nama_lokasi VARCHAR(100) NOT NULL AFTER kode_lokasi');
        DB::statement('ALTER TABLE jenis_kategori MODIFY COLUMN nama_jenis VARCHAR(100) NOT NULL AFTER kode_awalan');
        DB::statement('ALTER TABLE kategori_aset MODIFY COLUMN nama VARCHAR(100) NOT NULL AFTER kode');
    }
};
