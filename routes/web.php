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

Route::get('/students/bulk-registration', function () {
    return Inertia::render('Students/BulkRegistration');
})->name('students.bulk-registration');

Route::get('/students/bulk-image-upload', function () {
    return Inertia::render('Students/BulkImageUpload');
})->name('students.bulk-image-upload');

Route::get('/students/student-details', function () {
    return Inertia::render('Students/StudentDetails');
})->name('student.student-details');

Route::get('/students/archives', function () {
    return Inertia::render('Students/Archives');
})->name('students.archives');

Route::get('/students/edit', function () {
    return Inertia::render('Students/Edit');
})->name('students.edit');


//instructor
Route::get('/instructors', function () {
    return Inertia::render('Instructors/Index');
})->name('instructors.index');

Route::get('/instructors/single-registration', function () {
    return Inertia::render('Instructors/SingleRegistration');
})->name('instructors.single-registration');

Route::get('/instructors/bulk-image-upload', function () {
    return Inertia::render('Instructors/BulkImageUpload');
})->name('instructors.bulk-image-upload');

