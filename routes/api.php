<?php

use App\Http\Controllers\RedirectController;
use App\Http\Controllers\UrlController;
use Illuminate\Support\Facades\Route;

Route::post('/urls', [UrlController::class, 'store'])->middleware('hybrid.throttle:15,60,5,1');
Route::get('url/{code}', RedirectController::class)->middleware('hybrid.throttle:1000,60,100,20');
Route::get('health', function () {
    return 200; 
});
