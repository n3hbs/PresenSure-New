<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds compact B-tree indexes strictly focused on foreign key IDs and primary
     * lookup identifiers, avoiding storage bloat from unnecessary multi-column,
     * timestamp, or low-cardinality status indexes.
     */
    public function up(): void
    {
        // 1. user_roles: Primary Key ID and Role Foreign Key ID
        Schema::table('user_roles', function (Blueprint $table) {
            $table->primary('user_id');
            $table->index('role_id');
        });

        // 2. roles: Natural Key ID
        Schema::table('roles', function (Blueprint $table) {
            $table->unique('role_name');
        });

        // 3. departments & programs: Foreign Key and Code IDs
        Schema::table('departments', function (Blueprint $table) {
            $table->index('department_code');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->index('department_id');
            $table->index('program_code');
        });

        // 4. semesters: School Year Foreign Key ID
        Schema::table('semesters', function (Blueprint $table) {
            $table->index('school_year_id');
        });

        // 5. courses & course_blocks: Foreign Key and Code IDs
        Schema::table('courses', function (Blueprint $table) {
            $table->index('subject_code');
        });

        Schema::table('course_blocks', function (Blueprint $table) {
            $table->index('course_id');
            $table->index('semester_id');
        });

        // 6. rooms & ble_devices: Foreign Key IDs
        Schema::table('rooms', function (Blueprint $table) {
            $table->index('building_id');
        });

        Schema::table('ble_devices', function (Blueprint $table) {
            $table->index('room_id');
        });

        // 7. schedules: Foreign Key IDs
        Schema::table('schedules', function (Blueprint $table) {
            $table->index('course_block_id');
            $table->index('room_id');
            $table->index('semester_id');
        });

        // 8. students & instructors: Foreign Key IDs
        Schema::table('students', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('semester_id');
            $table->index('program_id');
        });

        Schema::table('instructors', function (Blueprint $table) {
            $table->unique('user_id');
            $table->index('department_id');
        });

        // 9. attendance_sessions: Foreign Key IDs
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->index('schedule_id');
            $table->index('period_id');
            $table->index('instructor_id');
            $table->index('ble_device_id');
        });

        // 10. Pivot & Relational Foreign Key IDs (Reverse lookup optimization)
        Schema::table('attendance_policy_courses', function (Blueprint $table) {
            $table->index('course_block_id');
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->index('permission_id');
        });

        Schema::table('user_permissions', function (Blueprint $table) {
            $table->index('permission_id');
        });

        // 11. excuse_requests & instructor_marks: User Foreign Key IDs
        Schema::table('excuse_requests', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('instructor_marks', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructor_marks', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('excuse_requests', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropIndex(['permission_id']);
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropIndex(['permission_id']);
        });

        Schema::table('attendance_policy_courses', function (Blueprint $table) {
            $table->dropIndex(['course_block_id']);
        });

        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropIndex(['schedule_id']);
            $table->dropIndex(['period_id']);
            $table->dropIndex(['instructor_id']);
            $table->dropIndex(['ble_device_id']);
        });

        Schema::table('instructors', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropIndex(['department_id']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['semester_id']);
            $table->dropIndex(['program_id']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['course_block_id']);
            $table->dropIndex(['room_id']);
            $table->dropIndex(['semester_id']);
        });

        Schema::table('ble_devices', function (Blueprint $table) {
            $table->dropIndex(['room_id']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['building_id']);
        });

        Schema::table('course_blocks', function (Blueprint $table) {
            $table->dropIndex(['course_id']);
            $table->dropIndex(['semester_id']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['subject_code']);
        });

        Schema::table('semesters', function (Blueprint $table) {
            $table->dropIndex(['school_year_id']);
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropIndex(['department_id']);
            $table->dropIndex(['program_code']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropIndex(['department_code']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['role_name']);
        });

        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropIndex(['role_id']);
            $table->dropPrimary(['user_id']);
        });
    }
};
