<?php

use App\Modules\Audit\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Audit Module API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'workspace:owner|admin'])->prefix('audit')->group(function () {
    Route::get('/logs', [AuditLogController::class, 'index'])->name('api.audit.logs');
});
