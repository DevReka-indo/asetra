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
        Schema::table('data_aset', function (Blueprint $table) {
            // Ubah id_divisi jadi nullable
            $table->unsignedInteger('id_divisi')->nullable()->change();

            // Tambahkan kolom org lain
            $table->unsignedInteger('id_director')->nullable()->after('tahun_kapitalisasi');
            $table->unsignedInteger('id_department')->nullable()->after('id_divisi');
            $table->unsignedInteger('id_section')->nullable()->after('id_department');
            $table->unsignedInteger('id_unit')->nullable()->after('id_section');

            // Tambah FK baru (hindarkan on_delete restrict yang keras jika null, kita pakai set null)
            $table->foreign('id_director')->references('id_director')->on('director')->onDelete('set null');
            $table->foreign('id_department')->references('id_department')->on('department')->onDelete('set null');
            $table->foreign('id_section')->references('id_section')->on('section')->onDelete('set null');
            $table->foreign('id_unit')->references('id_unit')->on('unit')->onDelete('set null');
        });

        Schema::table('log_aset', function (Blueprint $table) {
            $table->unsignedInteger('id_divisi')->nullable()->change();

            $table->unsignedInteger('id_director')->nullable()->after('lokasi_id');
            $table->unsignedInteger('id_department')->nullable()->after('id_divisi');
            $table->unsignedInteger('id_section')->nullable()->after('id_department');
            $table->unsignedInteger('id_unit')->nullable()->after('id_section');

            $table->foreign('id_director')->references('id_director')->on('director')->onDelete('set null');
            $table->foreign('id_department')->references('id_department')->on('department')->onDelete('set null');
            $table->foreign('id_section')->references('id_section')->on('section')->onDelete('set null');
            $table->foreign('id_unit')->references('id_unit')->on('unit')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_aset', function (Blueprint $table) {
            $table->dropForeign(['id_director']);
            $table->dropForeign(['id_department']);
            $table->dropForeign(['id_section']);
            $table->dropForeign(['id_unit']);

            $table->dropColumn(['id_director', 'id_department', 'id_section', 'id_unit']);
            // mengembalikan ke false nullable akan butuh nilai default
        });

        Schema::table('log_aset', function (Blueprint $table) {
            $table->dropForeign(['id_director']);
            $table->dropForeign(['id_department']);
            $table->dropForeign(['id_section']);
            $table->dropForeign(['id_unit']);

            $table->dropColumn(['id_director', 'id_department', 'id_section', 'id_unit']);
        });
    }
};
