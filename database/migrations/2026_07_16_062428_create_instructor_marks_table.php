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
        Schema::create('instructor_marks', function (Blueprint $table) {
            $table->id('instructor_mark_id');
            $table->foreignId('attendance_record_id')
                ->constrained('attendance_records', 'attendance_record_id')
                ->cascadeOnDelete();
            $table->string('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
            $table->string('reason');
            $table->text('details')->nullable();
            $table->enum('status', ['present', 'late', 'absent', 'excused', 'rejected']);
            $table->timestamp('instructor_marked_at')->useCurrent();
            $table->timestamps();

            $table->index(['attendance_record_id', 'user_id']);
            $table->index(['status', 'instructor_marked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_marks');
    }
};
