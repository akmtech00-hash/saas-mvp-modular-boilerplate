<?php

declare(strict_types=1);

namespace App\Modules\Monitoring\Providers;

use App\Modules\Monitoring\Services\HealthCheckService;
use App\Modules\Monitoring\Services\MetricsCollector;
use Illuminate\Support\ServiceProvider;

class MonitoringServiceProvider extends ServiceProvider
{
    /**
     * Register monitoring services.
     */
    public function register(): void
    {
        $this->app->singleton(MetricsCollector::class, function () {
            return new MetricsCollector();
        });

        $this->app->alias(MetricsCollector::class, 'monitoring.metrics');

        $this->app->singleton(HealthCheckService::class, function () {
            return new HealthCheckService();
        });
    }

    /**
     * Bootstrap monitoring resources.
     */
    public function boot(): void
    {
        //
    }
}
