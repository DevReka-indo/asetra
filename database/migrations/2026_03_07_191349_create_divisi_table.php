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
        Schema::create('divisi', function (Blueprint $table) {
            $table->increments('id_divisi');
            $table->unsignedInteger('director_id_director')->nullable();
            $table->string('nm_divisi', 200)->nullable();
            $table->string('kode_divisi', 10)->nullable();

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
        Schema::dropIfExists('divisi');
    }
};
