<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Sisipkan data default ke jenis_kategori
        DB::table('jenis_kategori')->insert([
            [
                'kode_awalan'  => '1',
                'nama_jenis'   => 'Aset Tetap',
                'warna_badge'  => 'danger',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'kode_awalan'  => '2',
                'nama_jenis'   => 'Inventaris',
                'warna_badge'  => 'primary',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);

        // Ambil ID jenis_kategori yang baru dibuat
        $idAsetTetap   = DB::table('jenis_kategori')->where('kode_awalan', '1')->value('id');
        $idInventaris  = DB::table('jenis_kategori')->where('kode_awalan', '2')->value('id');

        // Tambahkan kolom jenis_kategori_id ke kategori_aset
        Schema::table('kategori_aset', function (Blueprint $table) {
            $table->unsignedBigInteger('jenis_kategori_id')->nullable()->after('nama');
        });

        // Migrasikan data lama berdasarkan kolom tipe
        DB::table('kategori_aset')
            ->where('tipe', 'aset_tetap')
            ->update(['jenis_kategori_id' => $idAsetTetap]);

        DB::table('kategori_aset')
            ->where('tipe', 'inventaris')
            ->update(['jenis_kategori_id' => $idInventaris]);

        // Untuk data yang tipe-nya null/kosong, deteksi dari digit pertama kode
        DB::table('kategori_aset')
            ->whereNull('jenis_kategori_id')
            ->get()
            ->each(function ($row) use ($idAsetTetap, $idInventaris) {
                $prefix = substr((string) $row->kode, 0, 1);
                $jenis  = $prefix === '1' ? $idAsetTetap : $idInventaris;
                DB::table('kategori_aset')
                    ->where('id', $row->id)
                    ->update(['jenis_kategori_id' => $jenis]);
            });

        // Tambahkan foreign key constraint
        Schema::table('kategori_aset', function (Blueprint $table) {
            $table->foreign('jenis_kategori_id')
                  ->references('id')
                  ->on('jenis_kategori')
                  ->onDelete('set null');
        });

        // Hapus kolom tipe yang sudah tidak dibutuhkan
        Schema::table('kategori_aset', function (Blueprint $table) {
            $table->dropColumn('tipe');
        });
    }

    public function down(): void
    {
        // Tambah kembali kolom tipe
        Schema::table('kategori_aset', function (Blueprint $table) {
            $table->string('tipe', 50)->nullable()->after('nama');
        });

        // Isi kembali tipe dari relasi jenis_kategori
        $jenisList = DB::table('jenis_kategori')->get()->keyBy('id');
        DB::table('kategori_aset')->get()->each(function ($row) use ($jenisList) {
            if ($row->jenis_kategori_id && isset($jenisList[$row->jenis_kategori_id])) {
                $kodeAwalan = $jenisList[$row->jenis_kategori_id]->kode_awalan;
                $tipe = $kodeAwalan === '1' ? 'aset_tetap' : 'inventaris';
                DB::table('kategori_aset')->where('id', $row->id)->update(['tipe' => $tipe]);
            }
        });

        // Hapus foreign key dan kolom jenis_kategori_id
        Schema::table('kategori_aset', function (Blueprint $table) {
            $table->dropForeign(['jenis_kategori_id']);
            $table->dropColumn('jenis_kategori_id');
        });

        // Hapus data seed jenis_kategori
        DB::table('jenis_kategori')
            ->whereIn('kode_awalan', ['1', '2'])
            ->delete();
    }
};
