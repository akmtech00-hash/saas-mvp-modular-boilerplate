<?php

declare(strict_types=1);

use App\Modules\Admin\Controllers\AdminAiSandboxController;
use App\Modules\Admin\Controllers\AdminDashboardController;
use App\Modules\Admin\Controllers\AdminFeatureFlagController;
use App\Modules\Admin\Controllers\AdminMonitoringController;
use App\Modules\Admin\Controllers\AdminUserController;
use App\Modules\Admin\Controllers\AdminWorkspaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SaaS Super Administrator Web Console Routes
|--------------------------------------------------------------------------
|
| Protected by session authentication and EnsureSuperAdmin middleware.
|
*/

Route::prefix('admin')->name('admin.')->middleware(['admin'])->group(function () {
    // Executive Command Center Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Tenant & Workspace Governance
    Route::get('/workspaces', [AdminWorkspaceController::class, 'index'])->name('workspaces.index');
    Route::post('/workspaces/{id}/quota', [AdminWorkspaceController::class, 'updateQuota'])->name('workspaces.quota');
    Route::post('/workspaces/{id}/impersonate', [AdminWorkspaceController::class, 'impersonate'])->name('workspaces.impersonate');
    Route::post('/impersonate/stop', [AdminWorkspaceController::class, 'stopImpersonating'])->name('impersonate.stop');

    // User Directory & Role Delegation
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{id}/toggle-admin', [AdminUserController::class, 'toggleSuperAdmin'])->name('users.toggle-admin');

    // Feature Flags & Circuit Breakers
    Route::get('/features', [AdminFeatureFlagController::class, 'index'])->name('features.index');
    Route::post('/features/toggle', [AdminFeatureFlagController::class, 'toggleGlobal'])->name('features.toggle');
    Route::post('/features/override', [AdminFeatureFlagController::class, 'setOverride'])->name('features.override');
    Route::delete('/features/override/{id}', [AdminFeatureFlagController::class, 'deleteOverride'])->name('features.override.delete');

    // Live Infrastructure & Telemetry Cockpit
    Route::get('/monitoring', [AdminMonitoringController::class, 'index'])->name('monitoring.index');

    // AI Gateway Prompt Sandbox & LLM Playground
    Route::get('/ai-sandbox', [AdminAiSandboxController::class, 'index'])->name('ai.sandbox');
    Route::post('/ai-sandbox/run', [AdminAiSandboxController::class, 'run'])->name('ai.sandbox.run');
});
