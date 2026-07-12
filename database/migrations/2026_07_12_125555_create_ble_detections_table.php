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
        Schema::create('ble_detections', function (Blueprint $table) {
            $table->id('ble_detection_id');

            $table->foreignId('attendance_record_id')
                ->constrained('attendance_records', 'attendance_record_id')
                ->cascadeOnDelete();

            $table->string('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();

            $table->integer('rssi');
            $table->timestamp('detected_at')->useCurrent();

            $table->index(['attendance_record_id', 'detected_at']);
            $table->index(['user_id', 'detected_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ble_detections');
    }
};
