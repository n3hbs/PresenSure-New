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
        Schema::create('course_blocks', function (Blueprint $table) {
            $table->id('course_block_id');
            $table->unsignedBigInteger('course_id');
            $table->foreign('course_id')
                ->references('course_id')
                ->on('courses')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('semester_id');
            $table->foreign('semester_id')
                ->references('semester_id')
                ->on('semesters')
                ->cascadeOnDelete();
            $table->string('block_code');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_blocks');
    }
};
