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
        Schema::table('stock_opname', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'selesai'])->default('aktif')->after('keterangan');
        });

        Schema::table('stock_opname_detail', function (Blueprint $table) {
            $table->string('foto_temuan')->nullable()->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_opname_detail', function (Blueprint $table) {
            $table->dropColumn('foto_temuan');
        });

        Schema::table('stock_opname', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
