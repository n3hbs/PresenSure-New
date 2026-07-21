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
        Schema::create('users', function (Blueprint $table) {
            $table->string('user_id')->primary();
            $table->string('first_name');
            $table->string('middle_initial', 2)->nullable();
            $table->string('last_name');
            $table->string('suffix', 20)->nullable();
            $table->enum(
                'sex',
                ['male', 'female']
            );
            $table->string('password', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
