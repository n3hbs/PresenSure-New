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

// roles & permissions page
Route::get('/roles', function () {
    return Inertia::render('Roles/Index');
})->name('roles.index');

// student page
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

// instructor
Route::get('/instructors', function () {
    return Inertia::render('Instructors/Index');
})->name('instructors.index');

Route::get('/instructors/single-registration', function () {
    return Inertia::render('Instructors/SingleRegistration');
})->name('instructors.single-registration');

Route::get('/instructors/bulk-image-upload', function () {
    return Inertia::render('Instructors/BulkImageUpload');
})->name('instructors.bulk-image-upload');

Route::get('/instructors/instructor-details', function () {
    return Inertia::render('Instructors/InstructorDetails');
})->name('instructors.instructor-details');

Route::get('/instructors/archives', function () {
    return Inertia::render('Instructors/Archives');
})->name('instructors.archives');

Route::get('/instructors/edit', function () {
    return Inertia::render('Instructors/Edit');
})->name('instructors.edit');

// semesters page
Route::get('/semesters', function () {
    return Inertia::render('Semesters/Index');
})->name('semesters.index');

Route::get('/semesters/create', function () {
    return Inertia::render('Semesters/Create');
})->name('semesters.create');

Route::get('/semesters/edit', function () {
    return Inertia::render('Semesters/Edit');
})->name('semesters.edit');

Route::get('/semesters/archives', function () {
    return Inertia::render('Semesters/Archives');
})->name('semesters.archives');

Route::get('/semesters/semester-details', function () {
    return Inertia::render('Semesters/SemesterDetails');
})->name('semesters.semester-details');

Route::get('/semesters/{semester}/edit', function ($semester) {
    return Inertia::render('Semesters/Edit', ['semesterId' => $semester]);
})->name('semesters.edit.param');

// departments page
Route::get('/departments', function () {
    return Inertia::render('Departments/Index');
})->name('departments.index');

Route::get('/departments/create', function () {
    return Inertia::render('Departments/Create');
})->name('departments.create');

Route::get('/departments/edit', function () {
    return Inertia::render('Departments/Edit');
})->name('departments.edit');

Route::get('/departments/department-details', function () {
    return Inertia::render('Departments/DepartmentDetails');
})->name('departments.department-details');

Route::get('/departments/archives', function () {
    return Inertia::render('Departments/Archives');
})->name('departments.archives');

Route::get('/departments/{department}/edit', function ($department) {
    return Inertia::render('Departments/Edit', ['departmentId' => $department]);
})->name('departments.edit.param');
