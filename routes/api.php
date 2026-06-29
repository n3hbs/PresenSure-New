<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\ProgramResource;
use App\Models\Department;
use App\Models\Program;

Route::post('user/signin', [AuthController::class, 'signIn']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('student/registerStudent', [StudentController::class, 'create']);
    Route::get('student/getByActiveSemester', [StudentController::class, 'getStudentByActiveSemester']);
    Route::get('departments', fn () => DepartmentResource::collection(
        Department::orderBy('department_name')->get()
    ));
    Route::get('programs', fn () => ProgramResource::collection(
        Program::with('department')->orderBy('program_code')->get()
    ));
});
