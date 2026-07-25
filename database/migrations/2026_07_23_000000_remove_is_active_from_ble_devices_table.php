<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ble_devices', 'is_active')) {
            return;
        }

        Schema::table('ble_devices', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('ble_devices', 'is_active')) {
            return;
        }

        Schema::table('ble_devices', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
    }
};
