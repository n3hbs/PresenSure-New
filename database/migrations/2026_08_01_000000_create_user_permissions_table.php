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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->string('user_id');
            $table->foreignId('permission_id')
                ->constrained('permissions', 'permission_id')
                ->cascadeOnDelete();
            $table->boolean('is_granted')->default(true);
            $table->timestamp('assigned_at')->useCurrent();

            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->primary(['user_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
