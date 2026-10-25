<?php

declare(strict_types=1);

namespace App\Modules\AiGateway\Providers;

use App\Modules\AiGateway\Services\AiGatewayService;
use App\Modules\Metering\Services\CreditMeterService;
use Illuminate\Support\ServiceProvider;

class AiGatewayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiGatewayService::class, function ($app) {
            return new AiGatewayService($app->make(CreditMeterService::class));
        });
    }

    public function boot(): void
    {
        //
    }
}
