<?php

declare(strict_types=1);

namespace App\Modules\Metering\Providers;

use App\Modules\Metering\Services\CreditMeterService;
use Illuminate\Support\ServiceProvider;

class MeteringServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CreditMeterService::class, function () {
            return new CreditMeterService();
        });
    }

    public function boot(): void
    {
        //
    }
}
