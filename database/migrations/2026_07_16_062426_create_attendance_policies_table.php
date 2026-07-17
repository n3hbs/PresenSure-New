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
        Schema::create('attendance_policies', function (Blueprint $table) {
            $table->id('attendance_policy_id');
            $table->string('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
            $table->string('policy_name');
            $table->boolean('is_default')->default(false);
            $table->string('calculation_type');
            $table->unsignedInteger('late_threshold')->default(0);
            $table->unsignedInteger('absent_threshold')->default(0);
            $table->unsignedInteger('lates_to_absent')->default(0);
            $table->unsignedInteger('consecutive_absents_to_fail')->default(0);
            $table->decimal('attendance_weight', 5, 2)->default(0);
            $table->decimal('base_score', 5, 2)->default(0);
            $table->string('absent_detection');
            $table->string('late_detection');
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_policies');
    }
};
