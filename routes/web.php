<?php

use App\Modules\Commerce\Http\Controllers\Webhooks\PaymentWebhookController;
use App\Shared\Http\Controllers\Public\PublicHomeController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicHomeController::class)->name('welcome');
Route::post('/webhooks/payments/{provider}', PaymentWebhookController::class)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('payments.webhooks.handle');

require __DIR__.'/admin.php';
require __DIR__.'/student.php';
