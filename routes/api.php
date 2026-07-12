<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentController;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\SemesterResource;
use App\Models\Department;
use App\Models\Program;
use App\Services\SemesterService;

Route::post('user/signin', [AuthController::class, 'signIn']);

Route::middleware('auth:sanctum')->group(function () {


    //students
    Route::post('student/single-registration', [StudentController::class, 'create']);
    Route::get('student/getByActiveSemester', [StudentController::class, 'getStudentByActiveSemester']);
    Route::get('student/{user_id}', [StudentController::class, 'getstudentDetails']);
    Route::get('student/check-user/{user_id}', [StudentController::class, 'checkStudent']);

    //instructor
    Route::post('instructor/single-registration', [InstructorController::class, 'create']);

    //course
    Route::post('course', [CourseController::class, 'create']);
    Route::post('course-block/assign-users', [CourseController::class, 'assign']);
    Route::get('user/{user_id}/course-schedules', [CourseController::class, 'getUserCourseSchedule']);

    //schedule
    Route::post('schedule', [ScheduleController::class, 'create']);

    //period
    Route::post('period', [PeriodController::class, 'create']);


    //building
    Route::post('building', [BuildingController::class, 'create']);

    //room
    Route::post('room', [RoomController::class, 'create']);


    Route::get('departments', fn() => DepartmentResource::collection(
        Department::orderBy('department_name')->get()
    ));


    Route::get('programs', fn() => ProgramResource::collection(
        Program::with('department')->orderBy('program_code')->get()
    ));


    Route::get('semester/active', function (SemesterService $semesterService) {
        $semester = $semesterService->getActiveSemester();

        return response()->json([
            'data' => $semester
                ? new SemesterResource($semester->load('schoolYear'))
                : null,
        ]);
    });
});
