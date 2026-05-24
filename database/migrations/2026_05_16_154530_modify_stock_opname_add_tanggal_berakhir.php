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
            $table->renameColumn('tanggal', 'tanggal_mulai');
            $table->date('tanggal_berakhir')->nullable()->after('tanggal_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_opname', function (Blueprint $table) {
            $table->dropColumn('tanggal_berakhir');
            $table->renameColumn('tanggal_mulai', 'tanggal');
        });
    }
};
