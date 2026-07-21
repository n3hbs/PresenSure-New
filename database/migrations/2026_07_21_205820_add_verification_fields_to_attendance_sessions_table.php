<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->boolean('requires_periodic_verification')
                ->default(false);

            $table->timestamp('device_started_at')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'requires_periodic_verification',
                'device_started_at',
            ]);
        });
    }
};