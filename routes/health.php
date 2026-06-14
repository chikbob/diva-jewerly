<?php

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\LivenessCheckController;
use App\Http\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;

Route::get('/live', LivenessCheckController::class)->name('health.live');
Route::get('/ready', HealthCheckController::class)->name('health.ready');
Route::get('/up', HealthCheckController::class)->name('health.up');
Route::get('/metrics', MetricsController::class)
    ->middleware(\App\Http\Middleware\EnsureMetricsToken::class)
    ->name('metrics.index');
