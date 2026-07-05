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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id('room_id');

            $table->unsignedBigInteger('building_id');
            $table->foreign('building_id')
                ->references('building_id')
                ->on('buildings')
                ->cascadeOnDelete();

            $table->string('name');
            $table->integer('floor_no');
            $table->integer('capacity')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
