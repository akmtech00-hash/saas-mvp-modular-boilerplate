<?php

use App\Modules\Monitoring\Controllers\HealthController;
use App\Modules\Monitoring\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Observability & Monitoring Routes
|--------------------------------------------------------------------------
*/

Route::get('/health', [HealthController::class, 'ready'])->name('monitoring.health');
Route::get('/health/ready', [HealthController::class, 'ready'])->name('monitoring.health.ready');
Route::get('/health/live', [HealthController::class, 'live'])->name('monitoring.health.live');

Route::get('/metrics', [MetricsController::class, 'index'])->name('monitoring.metrics');
