<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\PeriodController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ResultController as AdminResultController;
use App\Http\Controllers\Participant\ExamController;
use App\Http\Controllers\Participant\ResultController as ParticipantResultController;

// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Change Password Routes (untuk semua user yang sudah login)
Route::middleware(['auth'])->group(function () {
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('change-password');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change-password.post');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('admin.dashboard');
    
    // User/Participant Management
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ])->except(['show']);
    
    // Period Management
    Route::resource('periods', PeriodController::class)->names([
        'index' => 'admin.periods.index',
        'create' => 'admin.periods.create',
        'store' => 'admin.periods.store',
        'show' => 'admin.periods.show',
        'edit' => 'admin.periods.edit',
        'update' => 'admin.periods.update',
        'destroy' => 'admin.periods.destroy',
    ]);

    // Category Management
    Route::resource('categories', CategoryController::class)->names([
        'index' => 'admin.categories.index',
        'create' => 'admin.categories.create',
        'store' => 'admin.categories.store',
        'show' => 'admin.categories.show',
        'edit' => 'admin.categories.edit',
        'update' => 'admin.categories.update',
        'destroy' => 'admin.categories.destroy',
    ]);

    // Question Management
    Route::resource('questions', QuestionController::class)->names([
        'index' => 'admin.questions.index',
        'create' => 'admin.questions.create',
        'store' => 'admin.questions.store',
        'show' => 'admin.questions.show',
        'edit' => 'admin.questions.edit',
        'update' => 'admin.questions.update',
        'destroy' => 'admin.questions.destroy',
    ]);

    // Results Management
    Route::get('/results', [AdminResultController::class, 'index'])->name('admin.results.index');
    Route::get('/results/period/{period}', [AdminResultController::class, 'show'])->name('admin.results.show');
    Route::get('/results/detail/{userAnswer}', [AdminResultController::class, 'detail'])->name('admin.results.detail');
    Route::post('/results/toggle-show-result/{period}', [AdminResultController::class, 'toggleShowResult'])->name('admin.results.toggle-show-result');
    Route::post('/results/toggle-show-grade/{period}', [AdminResultController::class, 'toggleShowGrade'])->name('admin.results.toggle-show-grade');
});

// Participant Routes
Route::middleware(['auth', 'role:participant'])->prefix('participant')->group(function () {
    Route::get('/exam', [ExamController::class, 'index'])->name('participant.exam.index');
    Route::get('/exam/start/{period}', [ExamController::class, 'start'])->name('participant.exam.start');
    Route::post('/exam/verify-password/{period}', [ExamController::class, 'verifyPassword'])->name('participant.exam.verify-password');
    Route::post('/exam/submit/{userAnswer}', [ExamController::class, 'submit'])->name('participant.exam.submit');
    Route::get('/exam/result/{userAnswer}', [ExamController::class, 'result'])->name('participant.exam.result');
});
