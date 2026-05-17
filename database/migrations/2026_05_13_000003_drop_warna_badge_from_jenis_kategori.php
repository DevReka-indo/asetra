<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_kategori', function (Blueprint $table) {
            $table->dropColumn('warna_badge');
        });
    }

    public function down(): void
    {
        Schema::table('jenis_kategori', function (Blueprint $table) {
            $table->string('warna_badge', 30)->default('primary')->after('nama_jenis');
        });
    }
};
