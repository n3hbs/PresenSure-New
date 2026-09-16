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
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::post('user/signin', [AuthController::class, 'signIn']);
Route::get('student/bulk-template', [StudentController::class, 'downloadTemplate']);

// Sanctum resolves the bearer token into $request->user(). Requests without a
// valid access token are rejected before any route in this group is executed.
Route::middleware('auth:sanctum')->group(function () {
    Route::post('user-profile/bulk-upload', [BulkImageUploadController::class, 'upload']);
    Broadcast::routes(['middleware' => ['auth:sanctum']]);

    // students
    Route::post('student', [StudentController::class, 'create']);
    Route::get('student/getByActiveSemester', [StudentController::class, 'getStudentByActiveSemester']);
    Route::get('student/archives', [StudentController::class, 'getArchivedStudents']);
    Route::get('student/{user_id}', [StudentController::class, 'getstudentDetails']);
    Route::patch('student/{user_id?}', [StudentController::class, 'update']);
    Route::delete('student/{user_id}', [StudentController::class, 'delete']);
    Route::post('student/{user_id}/archive', [StudentController::class, 'archive']);
    Route::post('student/{user_id}/restore', [StudentController::class, 'restore']);
    Route::get('student/check-user/{user_id}', [StudentController::class, 'checkStudent']);
    Route::post('student/bulk-extract', [StudentController::class, 'extractBulk']);
    Route::post('student/bulk-store', [StudentController::class, 'storeBulk']);

    // instructor
    Route::post('instructor', [InstructorController::class, 'create']);
    Route::get('instructors', [InstructorController::class, 'getAll']);

    // course
    Route::post('course', [CourseController::class, 'create']);
    Route::post('course-block', [CourseController::class, 'createBlock']);
    Route::post('course-block/assign-users', [CourseController::class, 'assign']);

    // schedule
    Route::post('schedule', [ScheduleController::class, 'create']);
    Route::get('user/{user_id}/course-schedules', [ScheduleController::class, 'getUserCourseSchedule']);
    Route::get('schedule/{schedule_id}/students', [ScheduleController::class, 'getScheduleStudentList']);

    // period
    Route::post('period', [PeriodController::class, 'create']);
    Route::get('period/active', [PeriodController::class, 'getActivePeriod']);

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
    Route::get('attendance-session/active/students', [AttendanceSessionController::class, 'getActiveSessionStudents']);
    Route::get('attendance-session/{attendance_session_id}/students', [AttendanceSessionController::class, 'getSessionStudents']);
    Route::put('attendance-session/stop', [AttendanceSessionController::class, 'stopAttendance']);
    Route::put('attendance-session/continue', [AttendanceSessionController::class, 'continueAttendance']);

    // attendance record
    Route::post('attendance-record', [AttendanceRecordController::class, 'create']);
    Route::get('attendance-record/check', [AttendanceRecordController::class, 'checkRecord']);

    // ble detection
    Route::post('ble-detection', [BleDetectionController::class, 'store']);
});
