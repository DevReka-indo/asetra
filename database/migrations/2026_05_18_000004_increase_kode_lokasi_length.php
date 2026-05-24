<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE lokasi_aset MODIFY COLUMN kode_lokasi VARCHAR(45) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE lokasi_aset MODIFY COLUMN kode_lokasi VARCHAR(20) NOT NULL");
    }
};
