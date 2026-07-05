<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            $table->dropColumn('instructor_id');
        });

        Schema::table('instructors', function (Blueprint $table) {
            $table->id('instructor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            $table->string('old_instructor_id')->nullable();
        });

        DB::statement('UPDATE "instructors" SET "old_instructor_id" = "instructor_id"::text');

        Schema::table('instructors', function (Blueprint $table) {
            $table->dropPrimary('instructors_instructor_id_primary');
            $table->dropColumn('instructor_id');
        });

        DB::statement('ALTER TABLE "instructors" RENAME COLUMN "old_instructor_id" TO "instructor_id"');
        DB::statement('ALTER TABLE "instructors" ALTER COLUMN "instructor_id" SET NOT NULL');
    }
};
