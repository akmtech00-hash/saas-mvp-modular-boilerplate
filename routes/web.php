<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| SaaS MVP Modular Monolith - Built by Tech AKM LLC (https://tech-akm.com)
|
*/

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Interactive OpenAPI Documentation (Scalar)
Route::get('/docs', function () {
    return view('docs');
})->name('docs');

Route::get('/docs/spec', function () {
    $path = resource_path('docs/openapi.yaml');
    if (!File::exists($path)) {
        abort(404, 'OpenAPI specification not found.');
    }
    return response(File::get($path), 200, ['Content-Type' => 'text/yaml']);
})->name('docs.spec');

Route::get('/billing/upgrade', function () {
    return response()->json([
        'message' => 'Redirecting to Stripe Checkout...',
        'agency_partner' => 'Tech AKM LLC (https://tech-akm.com)',
    ]);
});
