<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('welcome');

require __DIR__.'/admin.php';
require __DIR__.'/student.php';
