<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_aset', function (Blueprint $table) {
            $fks = collect(DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'data_aset'
                  AND TABLE_SCHEMA = DATABASE()
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                  AND COLUMN_NAME IN ('pic_monitoring', 'tanggal_cek_terakhir', 'dokumentasi_img', 'qr_code')
            "))->pluck('CONSTRAINT_NAME');

            foreach ($fks as $fk) {
                $table->dropForeign($fk);
            }
        });

        Schema::table('data_aset', function (Blueprint $table) {
            $cols = collect(DB::select("SHOW COLUMNS FROM data_aset"))
                ->pluck('Field')->toArray();

            $toDrop = array_intersect(
                ['tanggal_cek_terakhir', 'pic_monitoring', 'dokumentasi_img', 'qr_code'],
                $cols
            );

            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_aset', function (Blueprint $table) {
            $table->date('tanggal_cek_terakhir')->nullable()->after('status_aset');
            $table->unsignedBigInteger('pic_monitoring')->nullable()->after('pic_id');
            $table->string('dokumentasi_img')->nullable()->after('keterangan');
            $table->string('qr_code')->nullable()->after('dokumentasi_img');
        });
    }
};
