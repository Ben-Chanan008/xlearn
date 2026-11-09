<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseSectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\InstructorReviewController;
use App\Http\Controllers\MessageController;
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
Route::post('send-message', [MessageController::class, '__invoke'])->name('send-message')->middleware('auth');

Route::prefix('student')->middleware(['auth'])->group(function (){
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('role-redirect');
    Route::get('courses/{course:slug}', [DashboardController::class, 'show'])->name('courses.show')->can('view', 'course');
    Route::get('courses/{course:slug}/learn', [DashboardController::class, 'learn'])->name('courses.learn');
    Route::get('my-courses', [DashboardController::class, 'myCourses'])->name('my-courses');
    Route::get('courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll')->can('enroll', 'course');
    Route::post('courses/{course}/checkout', [CourseController::class, 'checkout'])->name('courses.checkout');
    Route::get('courses/{course}/grades', [CourseController::class, 'showGrades'])->name('course.grades');
    Route::get('courses/{course}/messages', [MessageController::class, 'showMessages'])->name('course.messages')->can('enrolled', 'course');
    Route::post('courses/{course}/review', [InstructorReviewController::class, 'store'])->name('course.review')->can('enrolled', 'course');
    Route::post('submit-assignment/{courseSectionContent}', [CourseController::class, 'submitAssignment'])->name('courses.submit-assignment');
});

Route::prefix('admin')->middleware(['auth'])->group(function (){
    Route::get('courses/create', [CourseController::class, 'create'])->name('courses.create')->can('create', App\Models\Course::class);
    Route::post('courses/store', [CourseController::class, 'store'])->name('courses.store');
    Route::delete('courses/{course}/delete', [CourseController::class, 'delete'])->name('courses.destroy')->can('manage', 'course');
    Route::get('courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit')->can('manage', 'course');
    Route::put('courses/{course}/update', [CourseController::class, 'update'])->name('courses.update')->can('manage', 'course');
});

Route::prefix('instructor')->middleware(['auth', 'role-redirect'])->group(function (){
    Route::get('my-courses', [DashboardController::class, 'instructorCourses'])->name('instructor.courses');
    Route::get('dashboard', [DashboardController::class, 'instructorDashboard'])->name('instructor.dashboard')->middleware('role-redirect');
    Route::get('courses/{course}/manage', [InstructorController::class, 'showCourseManagementPage'])->name('instructor.courses.manage')->can('manage', 'course');
    Route::get('courses/{course}/my-students', [InstructorController::class, 'manageStudents'])->name('instructor.courses.students')->can('manage', 'course');
    Route::get('courses/{course}/students/{student}/messages', [MessageController::class, 'conversation'])->name('instructor.courses.conversation')->can('manage', 'course');
    Route::get('courses/{course}/sections/create', [CourseSectionController::class, 'create'])->name('instructor.courses.sections.create')->can('manage', 'course');
    Route::post('courses/{course}/sections',[CourseSectionController::class, 'store'])->name('instructor.courses.sections.store')->can('manage', 'course');
    Route::get('courses/{course}/sections/{courseSection}/edit', [CourseSectionController::class, 'edit'])->name('instructor.courses.sections.edit')->can('manage', 'course')->scopeBindings();
    Route::put('courses/{course}/sections/{courseSection}', [CourseSectionController::class, 'update'])->name('instructor.courses.sections.update')->can('manage', 'course')->scopeBindings();
    Route::delete('courses/{course}/sections/{courseSection}', [CourseSectionController::class, 'destroy'])->name('instructor.courses.sections.destroy')->can('manage', 'course')->scopeBindings();
    Route::patch('courses/{course}/sections/{courseSection}/name', [CourseSectionController::class, 'rename'])->name('instructor.courses.sections.rename')->can('manage', 'course')->scopeBindings();
});