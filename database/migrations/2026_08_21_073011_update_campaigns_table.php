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
        Schema::table('campaigns', function (Blueprint $table) {
            // Ubah foreign key category_id tanpa cascadeOnDelete
            $table->dropForeign(['category_id']);

            $table->foreign('category_id')
                ->references('id')
                ->on('categories');

            // Tambah field baru
            $table->text('rejection_note')->nullable();
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // Rollback foreign key
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();

            // Hapus field baru
            $table->dropColumn(['rejection_note', 'reviewed_by', 'reviewed_at']);
        });
    }
};