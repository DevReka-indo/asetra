<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Struktur tabel stock opname
     */
    public function up(): void
    {
        // Header stock opname (satu periode)
        Schema::create('stock_opname', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('periode', 20);
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by');   // FK ke users.id
            $table->timestamps();

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');

            $table->index('periode');
        });

        // Detail per-aset dalam satu stock opname
        Schema::create('stock_opname_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_opname_id');
            $table->unsignedBigInteger('aset_id');
            $table->unsignedBigInteger('dicek_oleh');   // PIC monitoring — pengganti pic_monitoring di data_aset
            $table->date('tanggal_cek');
            $table->string('kondisi_temuan', 50);
            $table->string('lokasi_temuan', 150)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('stock_opname_id')
                  ->references('id')
                  ->on('stock_opname')
                  ->onDelete('cascade');

            $table->foreign('aset_id')
                  ->references('id')
                  ->on('data_aset')
                  ->onDelete('cascade');

            $table->foreign('dicek_oleh')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');

            $table->index(['stock_opname_id', 'aset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_detail');
        Schema::dropIfExists('stock_opname');
    }
};
