<?php

use App\Modules\Billing\Controllers\BillingController;
use App\Modules\Billing\Controllers\BillingWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Billing Module API Routes
|--------------------------------------------------------------------------
*/

// Public Pricing Plans
Route::get('/billing/plans', [BillingController::class, 'plans'])->name('api.billing.plans');

// Stripe Webhook Endpoint (Excluded from CSRF)
Route::post('/billing/stripe/webhook', [BillingWebhookController::class, 'handle'])->name('api.billing.webhook');

// Authenticated Workspace Billing Status
Route::middleware(['auth:sanctum', 'workspace'])->prefix('billing')->group(function () {
    Route::get('/status', [BillingController::class, 'status'])->name('api.billing.status');
});
