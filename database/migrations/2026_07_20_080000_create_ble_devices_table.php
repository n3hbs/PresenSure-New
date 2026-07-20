<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ble_devices', function (Blueprint $table) {
            $table->id('ble_device_id');
            $table->string('public_device_id')->unique();
            $table->foreignId('room_id')
                ->constrained('rooms', 'room_id')
                ->cascadeOnDelete();
            $table->text('device_secret');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ble_devices');
    }
};
