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
        Schema::create('attendance_policy_courses', function (Blueprint $table) {
            $table->foreignId('attendance_policy_id')
                ->constrained('attendance_policies', 'attendance_policy_id')
                ->cascadeOnDelete();
            $table->foreignId('course_block_id')
                ->constrained('course_blocks', 'course_block_id')
                ->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();

            $table->primary(['attendance_policy_id', 'course_block_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_policy_courses');
    }
};
