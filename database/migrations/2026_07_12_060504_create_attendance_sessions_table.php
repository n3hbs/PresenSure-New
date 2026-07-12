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
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id('attendance_session_id');

            $table->foreignId('schedule_id')
                ->constrained('schedules', 'schedule_id')
                ->cascadeOnDelete();

            $table->foreignId('period_id')
                ->constrained('periods', 'period_id')
                ->cascadeOnDelete();

            $table->string('instructor_id');
            $table->foreign('instructor_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();

            $table->enum('verification_mode', ['ble', 'face', 'ble_face']);

            $table->enum('ble_source_type', ['none', 'instructor_phone', 'room_beacon'])
                ->default('none');

            $table->unsignedBigInteger('beacon_id')->nullable();

            $table->string('broadcaster_user_id')->nullable();
            $table->foreign('broadcaster_user_id')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();

            $table->string('ble_broadcast_token')->nullable();
            $table->timestamp('ble_token_expires_at')->nullable();

            $table->enum('status', ['draft', 'active', 'ended', 'cancelled'])
                ->default('draft');

            $table->timestamp('start_at');
            $table->timestamp('end_at');

            $table->timestamps();

            $table->index(['schedule_id', 'period_id']);
            $table->index(['status', 'start_at', 'end_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
