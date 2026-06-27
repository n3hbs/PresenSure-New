<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('LandingPage/Index');
})->name('index');

Route::get('/students', function () {
    return Inertia::render('Students/Index');
})->name('students.index');


Route::get('/signIn', function () {
    return Inertia::render('SignIn/Index');
})->name('SignIn.index');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard/Index');
})->name('Dashboard.index');