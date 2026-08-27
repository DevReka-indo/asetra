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
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE lokasi_aset MODIFY COLUMN kode_lokasi VARCHAR(45) NOT NULL');

            return;
        }

        Schema::table('lokasi_aset', function (Blueprint $table) {
            $table->string('kode_lokasi', 45)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE lokasi_aset MODIFY COLUMN kode_lokasi VARCHAR(20) NOT NULL');

            return;
        }

        Schema::table('lokasi_aset', function (Blueprint $table) {
            $table->string('kode_lokasi', 20)->change();
        });
    }
};
