<?php

use App\Shared\Http\Controllers\Public\PublicHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicHomeController::class)->name('welcome');

require __DIR__.'/admin.php';
require __DIR__.'/student.php';
