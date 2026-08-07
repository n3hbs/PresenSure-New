<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('LandingPage/Index');
})->name('index');


Route::redirect('/signIn', '/signin');
Route::redirect('/login', '/signin');

Route::get('/signin', function () {
    return Inertia::render('SignIn/Index');
})->name('signin.index');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard/Index');
})->name('Dashboard.index');


//student page
Route::get('/students', function () {
    return Inertia::render('Students/Index');
})->name('students.index');

Route::get('/students/single-registration', function () {
    return Inertia::render('Students/SingleRegistration');
})->name('students.single-registration');

Route::get('/students/student-details', function () {
    return Inertia::render('Students/StudentDetails');
})->name('student.student-details');

//instructor
Route::get('/instructors', function () {
    return Inertia::render('Instructors/Index');
})->name('instructors.index');
