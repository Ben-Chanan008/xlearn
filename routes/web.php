<?php

use App\Http\Controllers\AuthController;
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

Route::prefix('student')->middleware(['auth'])->group(function (){
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
