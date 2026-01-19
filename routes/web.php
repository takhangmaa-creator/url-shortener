<?php

use App\Services\MetricsService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/metrics', function () {
    $metrics = app(MetricsService::class)->export();

    return response($metrics)
        ->header('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
});
