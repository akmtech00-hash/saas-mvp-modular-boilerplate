<?php

use App\Modules\Features\Controllers\FeatureController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Features Module API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'workspace'])->prefix('features')->group(function () {
    Route::get('/check', [FeatureController::class, 'check'])->name('api.features.check');
});
