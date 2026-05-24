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
        Schema::create('department_permission', function (Blueprint $table) {
            $table->unsignedInteger('department_id_department');
            $table->unsignedBigInteger('permission_id');

            $table->foreign('department_id_department')
                  ->references('id_department')
                  ->on('department')
                  ->onDelete('cascade');
                  
            $table->foreign('permission_id')
                  ->references('id')
                  ->on('permissions')
                  ->onDelete('cascade');

            $table->primary(['department_id_department', 'permission_id'], 'dept_perm_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_permission');
    }
};
