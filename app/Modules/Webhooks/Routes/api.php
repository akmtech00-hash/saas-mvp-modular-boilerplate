<?php

use App\Modules\Webhooks\Controllers\WebhookEndpointController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Outbound Webhooks Module API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'workspace'])->prefix('webhooks')->group(function () {
    Route::get('/', [WebhookEndpointController::class, 'index'])->name('api.webhooks.index');
    Route::post('/', [WebhookEndpointController::class, 'store'])->name('api.webhooks.store');
    Route::get('/{id}/deliveries', [WebhookEndpointController::class, 'deliveries'])->name('api.webhooks.deliveries');
    Route::post('/{id}/test', [WebhookEndpointController::class, 'testPing'])->name('api.webhooks.test');
    Route::delete('/{id}', [WebhookEndpointController::class, 'destroy'])->name('api.webhooks.destroy');
});
