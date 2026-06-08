<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('data_aset', function (Blueprint $table) {
            $table->integer('tahun_kapitalisasi')->nullable()->after('tanggal_kapitalisasi');
        });
 
        // Salin tahun dari tanggal_kapitalisasi ke tahun_kapitalisasi
        \Illuminate\Support\Facades\DB::statement("UPDATE data_aset SET tahun_kapitalisasi = YEAR(tanggal_kapitalisasi) WHERE tanggal_kapitalisasi IS NOT NULL");
 
        Schema::table('data_aset', function (Blueprint $table) {
            $table->dropColumn('tanggal_kapitalisasi');
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_aset', function (Blueprint $table) {
            $table->date('tanggal_kapitalisasi')->nullable()->after('tahun_kapitalisasi');
        });
 
        // Salin tahun kembali menjadi format tanggal YYYY-01-01
        \Illuminate\Support\Facades\DB::statement("UPDATE data_aset SET tanggal_kapitalisasi = CONCAT(tahun_kapitalisasi, '-01-01') WHERE tahun_kapitalisasi IS NOT NULL AND tahun_kapitalisasi != 0");
 
        Schema::table('data_aset', function (Blueprint $table) {
            $table->dropColumn('tahun_kapitalisasi');
        });
    }
};
