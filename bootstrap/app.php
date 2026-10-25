<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global Middlewares for Distributed Tracing and Monitoring
        $middleware->append(\App\Modules\Monitoring\Middleware\RequestCorrelationIdMiddleware::class);
        $middleware->append(\App\Modules\Monitoring\Middleware\PrometheusHttpMetricsMiddleware::class);

        // Named Route Middleware Aliases
        $middleware->alias([
            'workspace' => \App\Modules\Workspace\Middleware\EnsureWorkspaceAccess::class,
            'subscribed' => \App\Modules\Billing\Middleware\EnsureSubscriptionActive::class,
            'feature' => \App\Modules\Features\Middleware\EnsureFeatureActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Exception reporting & Sentry / monitoring telemetry hook
        $exceptions->report(function (\Throwable $e) {
            if (app()->bound('monitoring.metrics')) {
                app('monitoring.metrics')->incrementExceptionCount(get_class($e));
            }
        });
    })
    ->create();
