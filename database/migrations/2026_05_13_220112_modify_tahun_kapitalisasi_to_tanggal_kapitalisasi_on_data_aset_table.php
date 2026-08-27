<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('data_aset', function (Blueprint $table) {
            $table->date('tanggal_kapitalisasi')->nullable()->after('tahun_kapitalisasi');
        });

        $statement = DB::getDriverName() === 'sqlite'
            ? "UPDATE data_aset SET tanggal_kapitalisasi = CAST(tahun_kapitalisasi AS TEXT) || '-01-01' WHERE tahun_kapitalisasi IS NOT NULL AND tahun_kapitalisasi != 0"
            : "UPDATE data_aset SET tanggal_kapitalisasi = CONCAT(tahun_kapitalisasi, '-01-01') WHERE tahun_kapitalisasi IS NOT NULL AND tahun_kapitalisasi != 0";

        DB::statement($statement);

        Schema::table('data_aset', function (Blueprint $table) {
            $table->dropColumn('tahun_kapitalisasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_aset', function (Blueprint $table) {
            $table->integer('tahun_kapitalisasi')->nullable()->after('tanggal_kapitalisasi');
        });

        $statement = DB::getDriverName() === 'sqlite'
            ? "UPDATE data_aset SET tahun_kapitalisasi = CAST(strftime('%Y', tanggal_kapitalisasi) AS INTEGER) WHERE tanggal_kapitalisasi IS NOT NULL"
            : 'UPDATE data_aset SET tahun_kapitalisasi = YEAR(tanggal_kapitalisasi) WHERE tanggal_kapitalisasi IS NOT NULL';

        DB::statement($statement);

        Schema::table('data_aset', function (Blueprint $table) {
            $table->dropColumn('tanggal_kapitalisasi');
        });
    }
};
