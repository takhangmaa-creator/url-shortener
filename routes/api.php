<?php

use App\Http\Controllers\RedirectController;
use App\Http\Controllers\UrlController;
use Illuminate\Support\Facades\Route;

Route::post('/urls', [UrlController::class, 'store'])->middleware('throttle:shorten');
Route::get('url/{code}', RedirectController::class)->middleware('throttle:redirect');
Route::get('health', function () {
    return 200;
});
