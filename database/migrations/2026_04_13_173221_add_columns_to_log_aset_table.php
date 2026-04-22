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
        Schema::table('log_aset', function (Blueprint $table) {
            $table->unsignedInteger('lokasi_id')->nullable()->after('keterangan');
            $table->unsignedInteger('id_divisi')->nullable()->after('lokasi_id');
            $table->string('foto_bukti')->nullable()->after('id_divisi');

            $table->foreign('lokasi_id')->references('lokasi_id')->on('lokasi_aset')->onDelete('set null');
            $table->foreign('id_divisi')->references('id_divisi')->on('divisi')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_aset', function (Blueprint $table) {
            $table->dropForeign(['lokasi_id']);
            $table->dropForeign(['id_divisi']);
            $table->dropColumn(['lokasi_id', 'id_divisi', 'foto_bukti']);
        });
    }
};
