<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AttendanceRecordController;
use App\Http\Controllers\AttendanceSessionController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::post('user/signin', [AuthController::class, 'signIn']);

// Sanctum resolves the bearer token into $request->user(). Requests without a
// valid access token are rejected before any route in this group is executed.
Route::middleware('auth:sanctum')->group(function () {

    // students
    Route::post('student', [StudentController::class, 'create']);
    Route::get('student/getByActiveSemester', [StudentController::class, 'getStudentByActiveSemester']);
    Route::get('student/{user_id}', [StudentController::class, 'getstudentDetails']);
    Route::get('student/check-user/{user_id}', [StudentController::class, 'checkStudent']);

    // instructor
    Route::post('instructor', [InstructorController::class, 'create']);

    // course
    Route::post('course', [CourseController::class, 'create']);
    Route::post('course-block', [CourseController::class, 'createBlock']);
    Route::post('course-block/assign-users', [CourseController::class, 'assign']);

    // schedule
    Route::post('schedule', [ScheduleController::class, 'create']);
    Route::get('user/{user_id}/course-schedules', [ScheduleController::class, 'getUserCourseSchedule']);

    // period
    Route::post('period', [PeriodController::class, 'create']);

    // building
    Route::post('building', [BuildingController::class, 'create']);

    // room
    Route::post('room', [RoomController::class, 'create']);

    // department
    Route::get('departments', [DepartmentController::class, 'index']);

    // program
    Route::get('programs', [ProgramController::class, 'index']);

    // semester
    Route::get('semester/active', [SemesterController::class, 'getActiveSemester']);

    // Send validated input and the authenticated instructor into the create flow.
    Route::post('attendance-session', [AttendanceSessionController::class, 'create']);
    Route::get('attendance-session/active', [AttendanceSessionController::class, 'checkActive']);
    Route::put('attendance-session/stop', [AttendanceSessionController::class, 'stopAttendance']);
    Route::put('attendance-session/continue', [AttendanceSessionController::class, 'continueAttendance']);

    // attendance record
    Route::post('attendance-record', [AttendanceRecordController::class, 'create']);
    Route::get('attendance-record/check', [AttendanceRecordController::class, 'checkRecord']);
});
