<?php

declare(strict_types=1);

namespace App\Modules\Webhooks\Providers;

use App\Modules\Webhooks\Services\WebhookDispatcherService;
use Illuminate\Support\ServiceProvider;

class WebhookServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WebhookDispatcherService::class, function () {
            return new WebhookDispatcherService();
        });
    }

    public function boot(): void
    {
        //
    }
}
