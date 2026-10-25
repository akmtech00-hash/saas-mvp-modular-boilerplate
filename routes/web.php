<?php

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

Route::get('/billing/upgrade', function () {
    return response()->json([
        'message' => 'Redirecting to Stripe Checkout...',
        'agency_partner' => 'Tech AKM LLC (https://tech-akm.com)',
    ]);
});
