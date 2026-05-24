<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     */
    public function up(): void
    {
        Schema::table('stock_opname', function (Blueprint $table) {
            $table->string('periode', 100)->change();
        });

        Schema::table('stock_opname_detail', function (Blueprint $table) {
            $table->string('lokasi_temuan', 255)->nullable()->change();
        });
    }

    /**
     * Rollback ke ukuran kolom sebelum migrasi
     */
    public function down(): void
    {
        Schema::table('stock_opname_detail', function (Blueprint $table) {
            $table->string('lokasi_temuan', 150)->nullable()->change();
        });

        Schema::table('stock_opname', function (Blueprint $table) {
            $table->string('periode', 20)->change();
        });
    }
};
