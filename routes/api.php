<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\StudentController;

Route::post('signin', [AuthController::class, 'signIn']);
Route::post('registerStudent', [StudentController::class, 'singleStudentRegistration']);
