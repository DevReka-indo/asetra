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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // pivot table: section_permission
        Schema::create('section_permission', function (Blueprint $table) {
            $table->unsignedInteger('section_id_section');
            $table->unsignedBigInteger('permission_id');

            $table->foreign('section_id_section')->references('id_section')->on('section')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            $table->primary(['section_id_section', 'permission_id']);
        });

        // pivot table: role_permission
        Schema::create('role_permission', function (Blueprint $table) {
            $table->unsignedInteger('role_id_role');
            $table->unsignedBigInteger('permission_id');

            $table->foreign('role_id_role')->references('id_role')->on('role')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            $table->primary(['role_id_role', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('section_permission');
        Schema::dropIfExists('permissions');
    }
};
