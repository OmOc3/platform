<?php

use App\Modules\Students\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Modules\Students\Http\Controllers\Auth\NewPasswordController;
use App\Modules\Students\Http\Controllers\Auth\PasswordResetLinkController;
use App\Modules\Students\Http\Controllers\Auth\RegisteredStudentController;
use App\Modules\Students\Http\Controllers\Student\AttendanceHistoryController;
use App\Modules\Students\Http\Controllers\Student\BookOrderHistoryController;
use App\Modules\Students\Http\Controllers\Student\DashboardController;
use App\Modules\Students\Http\Controllers\Student\PaymentHistoryController;
use App\Modules\Students\Http\Controllers\Student\PortalPlaceholderController;
use App\Modules\Students\Http\Controllers\Student\ProfileController;
use App\Modules\Support\Http\Controllers\Student\ComplaintController;
use Illuminate\Support\Facades\Route;

Route::prefix('student')->name('student.')->group(function (): void {
    Route::middleware('guest:student')->group(function (): void {
        Route::get('/register', [RegisteredStudentController::class, 'create'])->name('register');
        Route::post('/register', [RegisteredStudentController::class, 'store'])->name('register.store');
        Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
        Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
        Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
    });

    Route::middleware(['auth:student', 'student.portal'])->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/payments', PaymentHistoryController::class)->name('payments.index');
        Route::get('/book-orders', BookOrderHistoryController::class)->name('book-orders.index');
        Route::get('/attendance', AttendanceHistoryController::class)->name('attendance.index');

        Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
        Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');

        Route::get('/lectures', [PortalPlaceholderController::class, 'show'])->defaults('section', 'lectures')->name('lectures.index');
        Route::get('/packages', [PortalPlaceholderController::class, 'show'])->defaults('section', 'packages')->name('packages.index');
        Route::get('/books', [PortalPlaceholderController::class, 'show'])->defaults('section', 'books')->name('books.index');
        Route::get('/forum', [PortalPlaceholderController::class, 'show'])->defaults('section', 'forum')->name('forum.index');
        Route::get('/mistakes', [PortalPlaceholderController::class, 'show'])->defaults('section', 'mistakes')->name('mistakes.index');
        Route::get('/cart', [PortalPlaceholderController::class, 'show'])->defaults('section', 'cart')->name('cart.index');
    });
});
