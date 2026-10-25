<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Providers;

use App\Modules\Workspace\Services\TenantContext;
use Illuminate\Support\ServiceProvider;

class WorkspaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, function () {
            return new TenantContext();
        });
    }

    public function boot(): void
    {
        //
    }
}
