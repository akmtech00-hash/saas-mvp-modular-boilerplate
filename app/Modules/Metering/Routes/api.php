<?php

use App\Modules\Metering\Controllers\CreditMeterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AI Metering Module API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('meter')->group(function () {
    Route::get('/status', [CreditMeterController::class, 'status'])->name('api.meter.status');
    Route::post('/deduct', [CreditMeterController::class, 'deduct'])->name('api.meter.deduct');
});
