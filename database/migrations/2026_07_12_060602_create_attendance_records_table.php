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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id('attendance_record_id');

            $table->foreignId('attendance_session_id')
                ->constrained('attendance_sessions', 'attendance_session_id')
                ->cascadeOnDelete();

            $table->string('student_id');
            $table->foreign('student_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();

            $table->boolean('ble_verified')->default(false);
            $table->boolean('face_verified')->default(false);
            $table->boolean('presence_verified')->default(false);

            $table->timestamp('ble_verified_at')->nullable();
            $table->timestamp('face_verified_at')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->enum('status', ['pending', 'present', 'late', 'absent', 'rejected'])
                ->default('pending');

            $table->timestamps();

            $table->unique(['attendance_session_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
