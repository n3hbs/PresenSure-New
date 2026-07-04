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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->unsignedBigInteger('course_block_id');
            $table->foreign('course_block_id')
                ->references('course_block_id')
                ->on('course_blocks')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('semester_id');
            $table->foreign('semester_id')
                ->references('semester_id')
                ->on('semesters')
                ->cascadeOnDelete();
            $table->string('block_code');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
