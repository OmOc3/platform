<?php

use App\Shared\Http\Controllers\StudentShellPreviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('student')->name('student.')->group(function (): void {
    Route::get('/preview', StudentShellPreviewController::class)->name('preview');
});
