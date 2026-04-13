<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename id_lokasi_kepemilikan to sumber_kepemilikan_id 
     */
    public function up(): void
    {
        Schema::table('data_aset', function (Blueprint $table) {
            $fks = collect(DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'data_aset'
                  AND TABLE_SCHEMA = DATABASE()
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                  AND COLUMN_NAME = 'id_lokasi_kepemilikan'
            "))->pluck('CONSTRAINT_NAME');

            foreach ($fks as $fk) {
                $table->dropForeign($fk);
            }
        });

        Schema::table('data_aset', function (Blueprint $table) {
            $table->renameColumn('id_lokasi_kepemilikan', 'sumber_kepemilikan_id');
        });

        Schema::table('data_aset', function (Blueprint $table) {
            $table->foreign('sumber_kepemilikan_id')
                  ->references('id')
                  ->on('sumber_kepemilikan')
                  ->onDelete('set null'); 
        });
    }

    public function down(): void
    {
        Schema::table('data_aset', function (Blueprint $table) {
            $table->dropForeign(['sumber_kepemilikan_id']);
            $table->renameColumn('sumber_kepemilikan_id', 'id_lokasi_kepemilikan');
        });
        
        
    }
};
