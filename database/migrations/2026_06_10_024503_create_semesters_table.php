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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id('semester_id');
            $table->unsignedBigInteger('school_year_id');
            $table->foreign('school_year_id')
                ->references('school_year_id')
                ->on('school_years')
                ->cascadeOnDelete();
            $table->enum('term', [
                'First Semester',
                'Second Semester',
                'Summer'
            ]);
            $table->date('semester_start');
            $table->date('semester_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
