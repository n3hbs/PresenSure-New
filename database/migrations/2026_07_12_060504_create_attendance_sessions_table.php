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

            $table->string('session_code', 16)->unique();

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
                
            $table->foreignId('ble_device_id')
                ->nullable()
                ->constrained('ble_devices', 'ble_device_id')
                ->nullOnDelete();

            $table->enum('verification_mode', ['ble', 'face', 'ble_face']);

            $table->string('ble_broadcast_token')->nullable();
            $table->timestamp('ble_token_expires_at')->nullable();

            $table->enum('status', ['draft', 'pending_device_confirmation', 'active', 'ended', 'cancelled'])
                ->default('draft');

            $table->timestamp('start_at');
            $table->timestamp('end_at');

            $table->timestamps();
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
