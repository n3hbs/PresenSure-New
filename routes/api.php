<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\StudentController;

Route::post('signin', [AuthController::class, 'signIn']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('student/registerStudent', [StudentController::class, 'create']);
    Route::get('student/getByActiveSemester', [StudentController::class, 'getStudentByActiveSemester']);
});
