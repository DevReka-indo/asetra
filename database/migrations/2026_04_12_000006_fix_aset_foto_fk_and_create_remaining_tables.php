<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perbaiki FK aset_foto yang gagal
     */
    public function up(): void
    {
        $existingForeignKey = collect(Schema::getForeignKeys('aset_foto'))
            ->first(fn (array $foreignKey): bool => $foreignKey['columns'] === ['aset_id']);

        if ($existingForeignKey !== null) {
            Schema::table('aset_foto', function (Blueprint $table) use ($existingForeignKey) {
                $table->dropForeign($existingForeignKey['name'] ?? $existingForeignKey['columns']);
            });
        }

        Schema::table('aset_foto', function (Blueprint $table) {
            $table->unsignedBigInteger('aset_id')->change();
        });

        // Tambahkan FK
        Schema::table('aset_foto', function (Blueprint $table) {
            $table->foreign('aset_id')
                ->references('id')
                ->on('data_aset')
                ->onDelete('cascade');
        });

        //  tabel log_aset
        if (! Schema::hasTable('log_aset')) {
            Schema::create('log_aset', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('aset_id');
                $table->date('tanggal_cek');
                $table->string('kondisi', 50);
                $table->string('status_aset', 50)->nullable();
                $table->text('keterangan')->nullable();
                $table->unsignedBigInteger('dicatat_oleh')->nullable();
                $table->timestamps();

                $table->foreign('aset_id')->references('id')->on('data_aset')->onDelete('cascade');
                $table->foreign('dicatat_oleh')->references('id')->on('users')->onDelete('set null');
                $table->index(['aset_id', 'tanggal_cek']);
            });
        }

        // tabel stock_opname
        if (! Schema::hasTable('stock_opname')) {
            Schema::create('stock_opname', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('periode', 20);
                $table->text('keterangan')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();

                $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
                $table->index('periode');
            });
        }

        if (! Schema::hasTable('stock_opname_detail')) {
            Schema::create('stock_opname_detail', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('stock_opname_id');
                $table->unsignedBigInteger('aset_id');
                $table->unsignedBigInteger('dicek_oleh');
                $table->date('tanggal_cek');
                $table->string('kondisi_temuan', 50);
                $table->string('lokasi_temuan', 150)->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->foreign('stock_opname_id')->references('id')->on('stock_opname')->onDelete('cascade');
                $table->foreign('aset_id')->references('id')->on('data_aset')->onDelete('cascade');
                $table->foreign('dicek_oleh')->references('id')->on('users')->onDelete('restrict');
                $table->index(['stock_opname_id', 'aset_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_detail');
        Schema::dropIfExists('stock_opname');
        Schema::dropIfExists('log_aset');

        Schema::table('aset_foto', function (Blueprint $table) {
            $table->dropForeign(['aset_id']);
        });
    }
};
