<?php

use App\Modules\Academic\Http\Controllers\Admin\GradeController;
use App\Modules\Academic\Http\Controllers\Admin\TrackController;
use App\Modules\Identity\Http\Controllers\Admin\AdminController;
use App\Modules\Identity\Http\Controllers\Admin\AuditLogController;
use App\Modules\Identity\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Modules\Identity\Http\Controllers\Admin\DashboardController;
use App\Modules\Identity\Http\Controllers\Admin\SettingController;
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
        Route::resource('admins', AdminController::class)->except(['show']);
        Route::get('/audit-logs', AuditLogController::class)->name('audit-logs.index');
    });
});
