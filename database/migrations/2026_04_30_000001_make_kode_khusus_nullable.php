<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_aset_khusus', function (Blueprint $table) {
            $table->string('kode_khusus', 10)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('jenis_aset_khusus', function (Blueprint $table) {
            $table->string('kode_khusus', 10)->nullable(false)->change();
        });
    }
};
