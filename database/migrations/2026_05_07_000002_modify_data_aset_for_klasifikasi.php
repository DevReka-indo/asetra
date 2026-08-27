<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom klasifikasi_aset_id
        Schema::table('data_aset', function (Blueprint $table) {
            $table->unsignedBigInteger('klasifikasi_aset_id')->nullable()->after('id');
        });

        // Tambah FK ke klasifikasi_aset
        Schema::table('data_aset', function (Blueprint $table) {
            $table->foreign('klasifikasi_aset_id')
                ->references('id')
                ->on('klasifikasi_aset')
                ->nullOnDelete();
        });

        $deprecatedForeignKeyColumns = ['jenis_aset_khusus_id', 'sumber_kepemilikan_id', 'kategori_id'];
        $foreignKeys = collect(Schema::getForeignKeys('data_aset'))
            ->filter(fn (array $foreignKey): bool => array_intersect($foreignKey['columns'], $deprecatedForeignKeyColumns) !== []);

        foreach ($foreignKeys as $foreignKey) {
            Schema::table('data_aset', function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign($foreignKey['name'] ?? $foreignKey['columns']);
            });
        }

        // Hapus kolom-kolom sebelummnya
        Schema::table('data_aset', function (Blueprint $table) {
            $toDrop = [];
            if (Schema::hasColumn('data_aset', 'jenis_aset_khusus_id')) {
                $toDrop[] = 'jenis_aset_khusus_id';
            }
            if (Schema::hasColumn('data_aset', 'sumber_kepemilikan_id')) {
                $toDrop[] = 'sumber_kepemilikan_id';
            }
            if (Schema::hasColumn('data_aset', 'kategori_id')) {
                $toDrop[] = 'kategori_id';
            }
            if (Schema::hasColumn('data_aset', 'kode_aset')) {
                $toDrop[] = 'kode_aset';
            }

            if (! empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_aset', function (Blueprint $table) {
            // Kembalikan kolom sebelumnya
            $table->string('kode_aset', 50)->nullable();
            $table->unsignedBigInteger('jenis_aset_khusus_id')->nullable();
            $table->unsignedBigInteger('sumber_kepemilikan_id')->nullable();
            $table->integer('kategori_id')->nullable();

            // Hapus kolom baru
            $table->dropForeign(['klasifikasi_aset_id']);
            $table->dropColumn('klasifikasi_aset_id');
        });
    }
};
