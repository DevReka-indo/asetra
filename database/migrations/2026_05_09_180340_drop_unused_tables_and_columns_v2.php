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
        // 1. Drop unused tables
        Schema::dropIfExists('jenis_aset_khusus');
        Schema::dropIfExists('jenis_aset_umum');
        Schema::dropIfExists('sumber_kepemilikan');
        Schema::dropIfExists('lokasi_kepemilikan');
        Schema::dropIfExists('master_jenis_aset');

        // 2. Remove columns from data_aset if they still exist
        Schema::table('data_aset', function (Blueprint $table) {
            $toDrop = [];
            if (Schema::hasColumn('data_aset', 'jenis_aset_umum_id')) $toDrop[] = 'jenis_aset_umum_id';
            if (Schema::hasColumn('data_aset', 'jenis_aset_khusus_id')) $toDrop[] = 'jenis_aset_khusus_id';
            if (Schema::hasColumn('data_aset', 'sumber_kepemilikan_id')) $toDrop[] = 'sumber_kepemilikan_id';
            if (Schema::hasColumn('data_aset', 'id_lokasi_kepemilikan')) $toDrop[] = 'id_lokasi_kepemilikan';

            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse for drop table in this case as it is a permanent cleanup requested by user
    }
};
