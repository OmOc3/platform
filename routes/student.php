<?php

use App\Modules\Academic\Http\Controllers\Student\LectureCatalogController;
use App\Modules\Academic\Http\Controllers\Student\ExamAttemptController;
use App\Modules\Academic\Http\Controllers\Student\LectureProgressController;
use App\Modules\Commerce\Http\Controllers\Student\BookCatalogController;
use App\Modules\Commerce\Http\Controllers\Student\CartController;
use App\Modules\Commerce\Http\Controllers\Student\CheckoutController;
use App\Modules\Commerce\Http\Controllers\Student\PackageCatalogController;
use App\Modules\Students\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Modules\Students\Http\Controllers\Auth\NewPasswordController;
use App\Modules\Students\Http\Controllers\Auth\PasswordResetLinkController;
use App\Modules\Students\Http\Controllers\Auth\RegisteredStudentController;
use App\Modules\Students\Http\Controllers\Student\AttendanceHistoryController;
use App\Modules\Students\Http\Controllers\Student\BookOrderHistoryController;
use App\Modules\Students\Http\Controllers\Student\DashboardController;
use App\Modules\Students\Http\Controllers\Student\MistakeController;
use App\Modules\Students\Http\Controllers\Student\PaymentHistoryController;
use App\Modules\Students\Http\Controllers\Student\ProfileController;
use App\Modules\Support\Http\Controllers\Student\ComplaintController;
use App\Modules\Support\Http\Controllers\Student\ForumThreadController;
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

        Route::get('/lectures', [LectureCatalogController::class, 'index'])->name('lectures.index');
        Route::get('/lectures/content/{lecture:slug}', [LectureCatalogController::class, 'showLecture'])->name('lectures.show');
        Route::post('/lectures/content/{lecture:slug}/progress/touch', [LectureProgressController::class, 'touch'])->name('lectures.progress.touch');
        Route::post('/lectures/content/{lecture:slug}/progress', [LectureProgressController::class, 'update'])->name('lectures.progress.update');
        Route::post('/lectures/content/{lecture:slug}/progress/complete', [LectureProgressController::class, 'complete'])->name('lectures.progress.complete');
        Route::post('/lectures/content/{lecture:slug}/checkpoints/{lectureCheckpoint}/reach', [LectureProgressController::class, 'reachCheckpoint'])->name('lectures.checkpoints.reach');
        Route::get('/exams/{exam:slug}', [LectureCatalogController::class, 'showExam'])->name('lectures.exams.show');
        Route::post('/exams/{exam:slug}/attempts', [ExamAttemptController::class, 'start'])->name('exam-attempts.start');
        Route::get('/exam-attempts/{examAttempt}', [ExamAttemptController::class, 'show'])->name('exam-attempts.show');
        Route::post('/exam-attempts/{examAttempt}/save', [ExamAttemptController::class, 'save'])->name('exam-attempts.save');
        Route::post('/exam-attempts/{examAttempt}/submit', [ExamAttemptController::class, 'submit'])->name('exam-attempts.submit');
        Route::get('/exam-attempts/{examAttempt}/result', [ExamAttemptController::class, 'result'])->name('exam-attempts.result');

        Route::get('/packages', [PackageCatalogController::class, 'index'])->name('packages.index');
        Route::get('/packages/{package}', [PackageCatalogController::class, 'show'])->name('packages.show');

        Route::get('/books', [BookCatalogController::class, 'index'])->name('books.index');
        Route::get('/books/{book}', [BookCatalogController::class, 'show'])->name('books.show');

        Route::get('/forum', [ForumThreadController::class, 'index'])->name('forum.index');
        Route::get('/forum/mine', [ForumThreadController::class, 'mine'])->name('forum.mine');
        Route::post('/forum', [ForumThreadController::class, 'store'])->name('forum.store');
        Route::get('/forum/{forumThread}', [ForumThreadController::class, 'show'])->name('forum.show');
        Route::post('/forum/{forumThread}/replies', [ForumThreadController::class, 'reply'])->name('forum.reply.store');

        Route::get('/mistakes', [MistakeController::class, 'index'])->name('mistakes.index');
        Route::get('/mistakes/{lecture:slug}', [MistakeController::class, 'show'])->name('mistakes.show');

        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
        Route::put('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

        Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
        Route::post('/checkout/prepare', [CheckoutController::class, 'prepare'])->name('checkout.prepare');
    });
});
