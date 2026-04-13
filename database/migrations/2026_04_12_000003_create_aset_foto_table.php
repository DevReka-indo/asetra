<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aset_foto', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('aset_id');
            $table->string('path_foto', 500);
            $table->string('keterangan', 150)->nullable();
            $table->tinyInteger('urutan')->default(1)->unsigned();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('aset_id')
                  ->references('id')
                  ->on('data_aset')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aset_foto');
    }
};
