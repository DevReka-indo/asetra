<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $deprecatedColumns = ['tanggal_cek_terakhir', 'pic_monitoring', 'dokumentasi_img', 'qr_code'];
        $foreignKeys = collect(Schema::getForeignKeys('data_aset'))
            ->filter(fn (array $foreignKey): bool => array_intersect($foreignKey['columns'], $deprecatedColumns) !== []);

        foreach ($foreignKeys as $foreignKey) {
            Schema::table('data_aset', function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign($foreignKey['name'] ?? $foreignKey['columns']);
            });
        }

        Schema::table('data_aset', function (Blueprint $table) {
            $toDrop = array_intersect(
                ['tanggal_cek_terakhir', 'pic_monitoring', 'dokumentasi_img', 'qr_code'],
                Schema::getColumnListing('data_aset')
            );

            if (! empty($toDrop)) {
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
