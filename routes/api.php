<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\SemesterResource;
use App\Models\Department;
use App\Models\Program;
use App\Services\SemesterService;

Route::post('user/signin', [AuthController::class, 'signIn']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('student/single-registration', [StudentController::class, 'create']);
    Route::get('student/getByActiveSemester', [StudentController::class, 'getStudentByActiveSemester']);
    Route::get('student/{user_id}', [StudentController::class, 'getstudentDetails']);

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
