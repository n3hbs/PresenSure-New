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
        Schema::create('user_course_blocks', function (Blueprint $table) {
            $table->id('user_course_block_id');

            $table->string('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreignId('course_block_id')
                ->constrained('course_blocks', 'course_block_id')
                ->cascadeOnDelete();

            $table->timestamp('assigned_at')->useCurrent();

            $table->unique(['user_id', 'course_block_id']);
            $table->index('course_block_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_course_blocks');
    }
};
