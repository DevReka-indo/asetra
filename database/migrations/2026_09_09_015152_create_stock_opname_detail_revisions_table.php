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
        if (Schema::hasTable('stock_opname_detail_revisions')) {
            if (! Schema::hasIndex('stock_opname_detail_revisions', ['stock_opname_detail_id', 'created_at'])) {
                Schema::table('stock_opname_detail_revisions', function (Blueprint $table) {
                    $table->index(
                        ['stock_opname_detail_id', 'created_at'],
                        'stock_detail_revision_created_idx',
                    );
                });
            }

            return;
        }

        Schema::create('stock_opname_detail_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_detail_id')
                ->constrained('stock_opname_detail')
                ->cascadeOnDelete();
            $table->foreignId('changed_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table->json('before_values');
            $table->json('after_values');
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['stock_opname_detail_id', 'created_at'],
                'stock_detail_revision_created_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_detail_revisions');
    }
};
