<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modular Architecture Configuration
    |--------------------------------------------------------------------------
    |
    | Defines registered domain modules within the SaaS Modular Monolith.
    | Each module registers its own routes, providers, migrations, and events.
    |
    */

    'path' => app_path('Modules'),

    'modules' => [
        'Auth' => [
            'enabled' => true,
            'provider' => App\Modules\Auth\Providers\AuthServiceProvider::class,
        ],
        'Workspace' => [
            'enabled' => true,
            'provider' => App\Modules\Workspace\Providers\WorkspaceServiceProvider::class,
        ],
        'Billing' => [
            'enabled' => true,
            'provider' => App\Modules\Billing\Providers\BillingServiceProvider::class,
        ],
        'Metering' => [
            'enabled' => true,
            'provider' => App\Modules\Metering\Providers\MeteringServiceProvider::class,
        ],
        'Monitoring' => [
            'enabled' => true,
            'provider' => App\Modules\Monitoring\Providers\MonitoringServiceProvider::class,
        ],
        'Features' => [
            'enabled' => true,
            'provider' => App\Modules\Features\Providers\FeatureServiceProvider::class,
        ],
        'Webhooks' => [
            'enabled' => true,
            'provider' => App\Modules\Webhooks\Providers\WebhookServiceProvider::class,
        ],
        'Audit' => [
            'enabled' => true,
            'provider' => App\Modules\Audit\Providers\AuditServiceProvider::class,
        ],
        'AiGateway' => [
            'enabled' => true,
            'provider' => App\Modules\AiGateway\Providers\AiGatewayServiceProvider::class,
        ],
        'Admin' => [
            'enabled' => true,
            'provider' => App\Modules\Admin\Providers\AdminServiceProvider::class,
        ],
    ],
];
