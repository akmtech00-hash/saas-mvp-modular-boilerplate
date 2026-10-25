<?php

use App\Modules\Workspace\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Workspace Module API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/workspaces', [WorkspaceController::class, 'index'])->name('api.workspaces.index');
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('api.workspaces.store');

    Route::middleware('workspace')->prefix('workspace')->group(function () {
        Route::get('/current', [WorkspaceController::class, 'show'])->name('api.workspace.show');
        Route::get('/members', [WorkspaceController::class, 'members'])->name('api.workspace.members');
        Route::post('/members/invite', [WorkspaceController::class, 'invite'])
            ->middleware('workspace:owner|admin')
            ->name('api.workspace.invite');
    });
});
