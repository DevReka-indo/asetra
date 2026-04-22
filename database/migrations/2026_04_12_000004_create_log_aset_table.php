<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel log_aset menyimpan riwayat pengecekan/perubahan kondisi aset.
     * tanggal_cek digunakan sebagai sumber accessor
     * getTanggalCekTerakhirAttribute() di model DataAset.
     */
    public function up(): void
    {
        Schema::create('log_aset', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('aset_id');
            $table->date('tanggal_cek');
            $table->string('kondisi', 50);
            $table->string('status_aset', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('dicatat_oleh')->nullable();
            $table->unsignedInteger('lokasi_id')->nullable();
            $table->unsignedInteger('id_director')->nullable();
            $table->unsignedInteger('id_divisi')->nullable();
            $table->unsignedInteger('id_department')->nullable();
            $table->unsignedInteger('id_section')->nullable();
            $table->unsignedInteger('id_unit')->nullable();
            $table->string('foto_bukti')->nullable();
            $table->timestamps();

            $table->foreign('aset_id')
                  ->references('id')
                  ->on('data_aset')
                  ->onDelete('cascade');

            $table->foreign('dicatat_oleh')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->index(['aset_id', 'tanggal_cek']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aset');
    }
};
