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
        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->rebuildSqliteTableWithIncrementingId();

            return;
        }

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
        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->rebuildSqliteTableWithStringId();

            return;
        }

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

    private function rebuildSqliteTableWithIncrementingId(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::rename('instructors', 'instructors_before_incrementing_id');

        Schema::create('instructors', function (Blueprint $table) {
            $table->id('instructor_id');
            $table->string('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')
                ->references('department_id')
                ->on('departments')
                ->cascadeOnDelete();
            $table->enum('status', ['Active', 'Inactive']);
            $table->timestamps();
        });

        DB::table('instructors')->insertUsing(
            ['user_id', 'department_id', 'status', 'created_at', 'updated_at'],
            DB::table('instructors_before_incrementing_id')->select(
                'user_id',
                'department_id',
                'status',
                'created_at',
                'updated_at'
            )
        );

        Schema::drop('instructors_before_incrementing_id');
        Schema::enableForeignKeyConstraints();
    }

    private function rebuildSqliteTableWithStringId(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::rename('instructors', 'instructors_before_string_id');

        Schema::create('instructors', function (Blueprint $table) {
            $table->string('instructor_id');
            $table->string('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')
                ->references('department_id')
                ->on('departments')
                ->cascadeOnDelete();
            $table->enum('status', ['Active', 'Inactive']);
            $table->timestamps();
        });

        DB::table('instructors')->insertUsing(
            ['instructor_id', 'user_id', 'department_id', 'status', 'created_at', 'updated_at'],
            DB::table('instructors_before_string_id')->select(
                'instructor_id',
                'user_id',
                'department_id',
                'status',
                'created_at',
                'updated_at'
            )
        );

        Schema::drop('instructors_before_string_id');
        Schema::enableForeignKeyConstraints();
    }
};
