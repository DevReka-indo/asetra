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
        if (!Schema::hasColumn('data_aset', 'penanggung_jawab_id')) {
            Schema::table('data_aset', function (Blueprint $table) {
                $table->unsignedBigInteger('penanggung_jawab_id')->nullable()->after('pic_id');
                $table->foreign('penanggung_jawab_id', 'fk_aset_penanggung_jawab')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_aset', function (Blueprint $table) {
            $table->dropForeign('fk_aset_penanggung_jawab');
            $table->dropColumn('penanggung_jawab_id');
        });
    }
};
