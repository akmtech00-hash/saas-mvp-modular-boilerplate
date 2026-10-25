<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Global API Routes
|--------------------------------------------------------------------------
|
| Module-specific routes are auto-discovered from app/Modules/{Name}/Routes/api.php
| and registered under the 'api/v1' prefix.
|
*/

Route::get('/', function () {
    return response()->json([
        'platform' => config('app.name'),
        'version' => '1.0.0',
        'status' => 'online',
        'documentation' => url('/docs'),
        'monitoring' => [
            'health' => url('/health'),
            'metrics' => url('/metrics'),
        ],
    ]);
});
