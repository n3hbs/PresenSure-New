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
        Schema::create('students', function (Blueprint $table) {
            $table->id('student_id');
            $table->string('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('program_id');
            $table->foreign('program_id')
                ->references('program_id')
                ->on('programs')
                ->cascadeOnDelete();
            $table->string('year');
            $table->string('block');
            $table->enum('status', [
                'Active,',
                'Inactive'
            ])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
