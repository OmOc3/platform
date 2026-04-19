<?php

use App\Modules\Academic\Http\Controllers\Admin\CurriculumSectionController;
use App\Modules\Academic\Http\Controllers\Admin\ExamController;
use App\Modules\Academic\Http\Controllers\Admin\ExamAttemptController;
use App\Modules\Academic\Http\Controllers\Admin\GradeController;
use App\Modules\Academic\Http\Controllers\Admin\LectureController;
use App\Modules\Academic\Http\Controllers\Admin\LectureSectionController;
use App\Modules\Academic\Http\Controllers\Admin\TrackController;
use App\Modules\Commerce\Http\Controllers\Admin\BookController;
use App\Modules\Commerce\Http\Controllers\Admin\OrderController;
use App\Modules\Commerce\Http\Controllers\Admin\PackageController;
use App\Modules\Identity\Http\Controllers\Admin\AdminController;
use App\Modules\Identity\Http\Controllers\Admin\AuditLogController;
use App\Modules\Identity\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Modules\Identity\Http\Controllers\Admin\DashboardController;
use App\Modules\Identity\Http\Controllers\Admin\SettingController;
use App\Modules\Students\Http\Controllers\Admin\MistakeController;
use App\Modules\Students\Http\Controllers\Admin\StudentController;
use App\Modules\Support\Http\Controllers\Admin\ForumThreadController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest:admin')->group(function (): void {
        Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth:admin')->group(function (): void {
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::resource('settings', SettingController::class)->except(['show']);
        Route::resource('grades', GradeController::class)->except(['show']);
        Route::resource('tracks', TrackController::class)->except(['show']);
        Route::resource('curriculum-sections', CurriculumSectionController::class)->except(['show']);
        Route::resource('lecture-sections', LectureSectionController::class)->except(['show']);
        Route::resource('lectures', LectureController::class)->except(['show']);
        Route::resource('exams', ExamController::class)->except(['show']);
        Route::get('/exam-attempts', [ExamAttemptController::class, 'index'])->name('exam-attempts.index');
        Route::get('/exam-attempts/{examAttempt}', [ExamAttemptController::class, 'show'])->name('exam-attempts.show');
        Route::resource('packages', PackageController::class)->except(['show']);
        Route::resource('books', BookController::class)->except(['show']);
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}/transition', [OrderController::class, 'transition'])->name('orders.transition');
        Route::resource('admins', AdminController::class)->except(['show']);
        Route::resource('students', StudentController::class)->only(['index', 'edit', 'update']);
        Route::get('/mistakes', [MistakeController::class, 'index'])->name('mistakes.index');
        Route::get('/mistakes/{mistakeItem}', [MistakeController::class, 'show'])->name('mistakes.show');
        Route::get('/forum-threads', [ForumThreadController::class, 'index'])->name('forum-threads.index');
        Route::get('/forum-threads/{forumThread}', [ForumThreadController::class, 'show'])->name('forum-threads.show');
        Route::put('/forum-threads/{forumThread}', [ForumThreadController::class, 'update'])->name('forum-threads.update');
        Route::post('/forum-threads/{forumThread}/reply', [ForumThreadController::class, 'reply'])->name('forum-threads.reply');
        Route::get('/audit-logs', AuditLogController::class)->name('audit-logs.index');
    });
});
