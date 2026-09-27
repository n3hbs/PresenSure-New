<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| PresenSure operates as a decoupled REST API backend. The web interface
| is served by the standalone React application (PresenSure-Web).
| Web traffic hitting the backend displays the project status and info.
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('index');

Route::fallback(function () {
    return view('welcome');
});
