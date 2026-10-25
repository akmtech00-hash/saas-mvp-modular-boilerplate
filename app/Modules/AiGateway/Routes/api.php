<?php

use App\Modules\AiGateway\Controllers\AiGatewayController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AI Gateway Module API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'workspace'])->prefix('ai')->group(function () {
    Route::post('/generate', [AiGatewayController::class, 'generate'])->name('api.ai.generate');
    Route::get('/models', [AiGatewayController::class, 'models'])->name('api.ai.models');
});
