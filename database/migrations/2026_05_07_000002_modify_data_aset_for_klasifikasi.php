<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        // Drop FK jenis_aset_khusus_id
        $fksJenis = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'data_aset'
              AND TABLE_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME IS NOT NULL
              AND COLUMN_NAME = 'jenis_aset_khusus_id'
        "))->pluck('CONSTRAINT_NAME');

        if ($fksJenis->isNotEmpty()) {
            Schema::table('data_aset', function (Blueprint $table) use ($fksJenis) {
                foreach ($fksJenis as $fk) {
                    $table->dropForeign($fk);
                }
            });
        }

        // Drop FK sumber_kepemilikan_id
        $fksSumber = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'data_aset'
              AND TABLE_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME IS NOT NULL
              AND COLUMN_NAME = 'sumber_kepemilikan_id'
        "))->pluck('CONSTRAINT_NAME');

        if ($fksSumber->isNotEmpty()) {
            Schema::table('data_aset', function (Blueprint $table) use ($fksSumber) {
                foreach ($fksSumber as $fk) {
                    $table->dropForeign($fk);
                }
            });
        }

        // Drop FK kategori_id
        $fksKategori = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'data_aset'
              AND TABLE_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME IS NOT NULL
              AND COLUMN_NAME = 'kategori_id'
        "))->pluck('CONSTRAINT_NAME');

        if ($fksKategori->isNotEmpty()) {
            Schema::table('data_aset', function (Blueprint $table) use ($fksKategori) {
                foreach ($fksKategori as $fk) {
                    $table->dropForeign($fk);
                }
            });
        }

        // Hapus kolom-kolom sebelummnya
        Schema::table('data_aset', function (Blueprint $table) {
            $toDrop = [];
            if (Schema::hasColumn('data_aset', 'jenis_aset_khusus_id')) $toDrop[] = 'jenis_aset_khusus_id';
            if (Schema::hasColumn('data_aset', 'sumber_kepemilikan_id')) $toDrop[] = 'sumber_kepemilikan_id';
            if (Schema::hasColumn('data_aset', 'kategori_id')) $toDrop[] = 'kategori_id';
            if (Schema::hasColumn('data_aset', 'kode_aset')) $toDrop[] = 'kode_aset';

            if (!empty($toDrop)) {
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
