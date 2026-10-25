<?php

declare(strict_types=1);

namespace App\Modules\Features\Providers;

use App\Modules\Features\Services\FeatureFlagService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FeatureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FeatureFlagService::class, function () {
            return new FeatureFlagService();
        });
    }

    public function boot(): void
    {
        // Custom Blade directive: @feature('api_access') ... @endfeature
        Blade::if('feature', function (string $featureKey) {
            return app(FeatureFlagService::class)->isEnabled($featureKey);
        });
    }
}
