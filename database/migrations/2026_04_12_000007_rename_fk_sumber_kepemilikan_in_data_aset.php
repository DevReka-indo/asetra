<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename id_lokasi_kepemilikan to sumber_kepemilikan_id
     */
    public function up(): void
    {
        $foreignKeys = collect(Schema::getForeignKeys('data_aset'))
            ->filter(fn (array $foreignKey): bool => $foreignKey['columns'] === ['id_lokasi_kepemilikan']);

        foreach ($foreignKeys as $foreignKey) {
            Schema::table('data_aset', function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign($foreignKey['name'] ?? $foreignKey['columns']);
            });
        }

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
