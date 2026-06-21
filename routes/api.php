<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\StudentController;

Route::post('signin', [AuthController::class, 'signIn']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('registerStudent', [StudentController::class, 'singleStudentRegistration']);
    Route::get('getStudentBySemester', [StudentController::class, 'getStudentByActiveSemester']);
});
