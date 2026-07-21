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
        Schema::create('programs', function (Blueprint $table) {
            $table->id('program_id');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')
                ->references('department_id')
                ->on('departments')
                ->cascadeOnDelete();
            $table->string('program_code');
            $table->string('program_name');
            $table->unsignedTinyInteger('program_years')->default(4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
