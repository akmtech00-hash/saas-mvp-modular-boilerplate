<?php

declare(strict_types=1);

namespace App\Modules\Billing\Providers;

use App\Modules\Billing\Services\StripeBillingService;
use Illuminate\Support\ServiceProvider;

class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StripeBillingService::class, function () {
            return new StripeBillingService();
        });
    }

    public function boot(): void
    {
        //
    }
}
