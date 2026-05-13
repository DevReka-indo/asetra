<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_perbaikan', function (Blueprint $table) {
            $table->id();

            // Aset yang dilaporkan
            $table->unsignedBigInteger('aset_id');
            $table->foreign('aset_id')
                  ->references('id')
                  ->on('data_aset')
                  ->onDelete('cascade');

            // User yang mengajukan
            $table->unsignedBigInteger('diajukan_oleh');
            $table->foreign('diajukan_oleh')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');

            // Data pengajuan
            $table->date('tanggal_pengajuan');
            $table->text('deskripsi_kerusakan');
            $table->string('foto_kerusakan')->nullable();
            $table->enum('tingkat_urgensi', ['rendah', 'sedang', 'tinggi'])->default('sedang');

            // Status lifecycle
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'selesai'])->default('menunggu');

            // Data review oleh admin Bagian Umum
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('diproses_oleh')->nullable();
            $table->foreign('diproses_oleh')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            $table->date('tanggal_diproses')->nullable();

            // Data penyelesaian perbaikan
            $table->string('kondisi_setelah', 50)->nullable();
            $table->date('tanggal_selesai')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['aset_id', 'status']);
            $table->index('diajukan_oleh');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_perbaikan');
    }
};
