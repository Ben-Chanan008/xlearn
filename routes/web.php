<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('sign-in', 'login')->name('login')->middleware('guest');
Route::post('login', [AuthController::class, 'login'])->name('login.post')->middleware('guest');

Route::view('register', 'register')->name('register')->middleware('guest');
Route::post('sign-up', [AuthController::class, 'register'])->name('register.post')->middleware('guest');

Route::get('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::view('forgot-password', 'auth.forgot-password')->name('password.request')->middleware('guest');

Route::prefix('student')->middleware(['auth'])->group(function (){
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('courses/{course:slug}', [DashboardController::class, 'show'])->name('courses.show');
    Route::get('my-courses', [DashboardController::class, 'myCourses'])->name('my-courses');

});

Route::prefix('admin')->middleware(['auth'])->group(function (){
    Route::get('courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('courses/store', [CourseController::class, 'store'])->name('courses.store');
});
