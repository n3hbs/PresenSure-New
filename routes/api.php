<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
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
use App\Http\Resources\SemesterResource;
use App\Services\SemesterService;

Route::post('user/signin', [AuthController::class, 'signIn']);

Route::middleware('auth:sanctum')->group(function () {


    //students
    Route::post('student', [StudentController::class, 'create']);
    Route::get('student/getByActiveSemester', [StudentController::class, 'getStudentByActiveSemester']);
    Route::get('student/{user_id}', [StudentController::class, 'getstudentDetails']);
    Route::get('student/check-user/{user_id}', [StudentController::class, 'checkStudent']);

    //instructor
    Route::post('instructor', [InstructorController::class, 'create']);

    //course
    Route::post('course', [CourseController::class, 'create']);
    Route::post('course-block', [CourseController::class, 'createBlock']);
    Route::post('course-block/assign-users', [CourseController::class, 'assign']);

    //schedule
    Route::post('schedule', [ScheduleController::class, 'create']);
    Route::get('user/{user_id}/course-schedules', [ScheduleController::class, 'getUserSchedule']);

    //period
    Route::post('period', [PeriodController::class, 'create']);


    //building
    Route::post('building', [BuildingController::class, 'create']);

    //room
    Route::post('room', [RoomController::class, 'create']);

    //department
    Route::get('departments', [DepartmentController::class, 'index']);

    //program
    Route::get('programs', [ProgramController::class, 'index']);

    //semester
    Route::get('semester/active', [SemesterController::class, 'getActiveSemester']);
});
