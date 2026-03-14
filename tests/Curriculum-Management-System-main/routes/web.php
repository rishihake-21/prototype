<?php

use App\Http\Controllers\SchemeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/scheme/create');
});

Route::get('/scheme/create', [SchemeController::class, 'create'])
    ->name('scheme.create');
Route::post('/scheme/store', [SchemeController::class, 'store'])->name('scheme.store');

Route::get('/scheme/{scheme}/levels/{level}/courses',
    [SchemeController::class, 'addCourses']
)->name('scheme.addCourses');

Route::post('/scheme/{scheme}/levels/{level}/courses',
    [SchemeController::class, 'storeCourses']
)->name('scheme.storeCourses');

Route::get('/scheme/{scheme}/summary', [SchemeController::class, 'summary'])
    ->name('scheme.summary');

Route::get('/scheme/{scheme}/page18', [SchemeController::class, 'page18'])
    ->name('scheme.page18');