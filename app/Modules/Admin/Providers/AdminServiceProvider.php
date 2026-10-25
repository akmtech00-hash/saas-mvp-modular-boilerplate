<?php

declare(strict_types=1);

namespace App\Modules\Admin\Providers;

use App\Modules\Admin\Commands\PromoteAdminCommand;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Register any Admin module services.
     */
    public function register(): void
    {
        // Admin bindings if necessary
    }

    /**
     * Bootstrap any Admin module services, commands, and views.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PromoteAdminCommand::class,
            ]);
        }
    }
}
