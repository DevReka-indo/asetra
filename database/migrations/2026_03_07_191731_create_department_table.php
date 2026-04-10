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
        Schema::create('department', function (Blueprint $table) {
             $table->increments('id_department'); 
             $table->string('name_department', 100);
             $table->string('kode_department', 11)->nullable();
             $table->unsignedInteger('divisi_id_divisi')->nullable();
             $table->unsignedInteger('director_id_director')->nullable();

            // Foreign key ke divisi
             $table->foreign('divisi_id_divisi')
                  ->references('id_divisi')
                  ->on('divisi')
                  ->onDelete('set null');

            // Foreign key ke director
            $table->foreign('director_id_director')
                  ->references('id_director')
                  ->on('director')
                  ->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department');
    }
};
