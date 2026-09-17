<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AttendanceRecordController;
use App\Http\Controllers\AttendanceSessionController;
use App\Http\Controllers\BleDetectionController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\BulkImageUploadController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('user/signin', [AuthController::class, 'signIn']);
Route::get('student/bulk-template', [StudentController::class, 'downloadTemplate']);

// Sanctum authenticated API routes
Route::middleware('auth:sanctum')->group(function () {
    Broadcast::routes(['middleware' => ['auth:sanctum']]);

    // Current authenticated user session details
    Route::get('user/me', [UserController::class, 'me']);

    // =========================================================================
    // TIER 1: CORE SYSTEM GOVERNANCE (Strictly Role-Guarded: Administrator Only)
    // Only users with the 'administrator' role can access these system-level endpoints.
    // =========================================================================
    Route::middleware('role:administrator')->group(function () {
        // Roles & Permissions Matrix
        Route::get('roles', [RoleController::class, 'index']);
        Route::get('permissions', [RoleController::class, 'permissions']);
        Route::put('roles/{role_id}/permissions', [RoleController::class, 'updateRolePermissions']);

        // User-Specific Direct Permission Overrides (by user_id)
        Route::get('users/search', [UserController::class, 'search']);
        Route::get('users/{user_id}/permissions', [UserPermissionController::class, 'getUserPermissions']);
        Route::put('users/{user_id}/permissions', [UserPermissionController::class, 'updateUserPermissions']);
        Route::delete('users/{user_id}/permissions', [UserPermissionController::class, 'resetUserPermissions']);

        // Core Academic & Facility Structure Setup
        Route::post('department', [DepartmentController::class, 'create']);
        Route::post('program', [ProgramController::class, 'create']);
        Route::post('period', [PeriodController::class, 'create']);
        Route::post('building', [BuildingController::class, 'create']);
        Route::post('room', [RoomController::class, 'create']);
    });

    // =========================================================================
    // TIER 2: OPERATIONAL ACTIONS (Governed by Permissions)
    // Administrators always have full access (universal bypass). Permissions can
    // be customized for roles or granted directly to specific users by user_id.
    // =========================================================================

    // --- STUDENT MANAGEMENT ---
    Route::middleware('permission:students.view')->group(function () {
        Route::get('student/getByActiveSemester', [StudentController::class, 'getStudentByActiveSemester']);
        Route::get('student/check-user/{user_id}', [StudentController::class, 'checkStudent']);
        Route::get('student/{user_id}', [StudentController::class, 'getstudentDetails']);
    });

    Route::middleware('permission:students.create')->group(function () {
        Route::post('student', [StudentController::class, 'create']);
        Route::post('student/bulk-extract', [StudentController::class, 'extractBulk']);
        Route::post('student/bulk-store', [StudentController::class, 'storeBulk']);
    });

    Route::middleware('permission:students.edit')->group(function () {
        Route::patch('student/{user_id?}', [StudentController::class, 'update']);
        Route::post('student/{user_id}', [StudentController::class, 'update']);
    });

    Route::middleware('permission:students.archive')->group(function () {
        Route::delete('student/{user_id}', [StudentController::class, 'delete']);
        Route::post('student/{user_id}/archive', [StudentController::class, 'archive']);
        Route::post('student/{user_id}/restore', [StudentController::class, 'restore']);
        Route::get('student/archives', [StudentController::class, 'getArchivedStudents']);
    });

    // --- INSTRUCTOR MANAGEMENT ---
    Route::middleware('permission:instructors.view')->group(function () {
        Route::get('instructors', [InstructorController::class, 'getAll']);
        Route::get('instructor/{user_id}', [InstructorController::class, 'getInstructorDetails']);
    });

    Route::middleware('permission:instructors.create')->group(function () {
        Route::post('instructor', [InstructorController::class, 'create']);
    });

    Route::middleware('permission:instructors.edit')->group(function () {
        Route::patch('instructor/{user_id?}', [InstructorController::class, 'update']);
        Route::post('instructor/{user_id}', [InstructorController::class, 'update']);
    });

    Route::middleware('permission:instructors.archive')->group(function () {
        Route::delete('instructor/{user_id}', [InstructorController::class, 'delete']);
        Route::post('instructor/{user_id}/archive', [InstructorController::class, 'archive']);
        Route::post('instructor/{user_id}/restore', [InstructorController::class, 'restore']);
        Route::get('instructor/archives', [InstructorController::class, 'getArchivedInstructors']);
    });

    // --- USER ASSETS & PASSWORDS ---
    Route::middleware('permission:students.create,instructors.create')->group(function () {
        Route::post('user-profile/bulk-upload', [BulkImageUploadController::class, 'upload']);
    });

    Route::middleware('permission:students.reset_password,instructors.reset_password')->group(function () {
        Route::post('user/{user_id}/reset-password', [UserController::class, 'resetPassword']);
    });

    // --- ACADEMIC COURSES & SCHEDULES ---
    Route::middleware('permission:courses.manage')->group(function () {
        Route::post('course', [CourseController::class, 'create']);
        Route::post('course-block', [CourseController::class, 'createBlock']);
        Route::post('course-block/assign-users', [CourseController::class, 'assign']);
    });

    Route::middleware('permission:schedules.manage')->group(function () {
        Route::post('schedule', [ScheduleController::class, 'create']);
    });

    // --- ATTENDANCE SESSIONS & CONTROL ---
    Route::middleware('permission:attendance.sessions.manage')->group(function () {
        Route::post('attendance-session', [AttendanceSessionController::class, 'create']);
        Route::put('attendance-session/stop', [AttendanceSessionController::class, 'stopAttendance']);
        Route::put('attendance-session/continue', [AttendanceSessionController::class, 'continueAttendance']);
    });

    // =========================================================================
    // TIER 3: COMMON & GENERAL AUTHENTICATED ENDPOINTS
    // Read-only catalog lookups, personal schedules, and check-ins for all active users.
    // =========================================================================
    Route::get('departments', [DepartmentController::class, 'index']);
    Route::get('programs', [ProgramController::class, 'index']);
    Route::get('semester/active', [SemesterController::class, 'getActiveSemester']);
    Route::get('period/active', [PeriodController::class, 'getActivePeriod']);
    Route::get('user/{user_id}/course-schedules', [ScheduleController::class, 'getUserCourseSchedule']);
    Route::get('schedule/{schedule_id}/students', [ScheduleController::class, 'getScheduleStudentList']);
    Route::get('attendance-session/active', [AttendanceSessionController::class, 'checkActive']);
    Route::get('attendance-session/active/students', [AttendanceSessionController::class, 'getActiveSessionStudents']);
    Route::get('attendance-session/{attendance_session_id}/students', [AttendanceSessionController::class, 'getSessionStudents']);
    Route::post('attendance-record', [AttendanceRecordController::class, 'create']);
    Route::get('attendance-record/check', [AttendanceRecordController::class, 'checkRecord']);
    Route::post('ble-detection', [BleDetectionController::class, 'store']);
});
