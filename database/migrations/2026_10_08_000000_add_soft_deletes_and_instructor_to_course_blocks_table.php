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
        Schema::table('course_blocks', function (Blueprint $table) {
            if (! Schema::hasColumn('course_blocks', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
            if (! Schema::hasColumn('course_blocks', 'instructor_id')) {
                $table->string('instructor_id')->nullable()->after('block_code');
                $table->foreign('instructor_id')
                    ->references('user_id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_blocks', function (Blueprint $table) {
            if (Schema::hasColumn('course_blocks', 'instructor_id')) {
                $table->dropForeign(['instructor_id']);
                $table->dropColumn('instructor_id');
            }
            if (Schema::hasColumn('course_blocks', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
