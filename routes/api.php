<?php

use App\Http\Controllers\RedirectController;
use App\Http\Controllers\UrlController;
use App\Services\MetricsService;
use Illuminate\Support\Facades\Route;

Route::post('/urls', [UrlController::class, 'store']);
Route::get('url/{code}', RedirectController::class);
