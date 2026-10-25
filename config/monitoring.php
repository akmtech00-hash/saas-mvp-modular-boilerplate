<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Observability & Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Central configuration for Health Checks, Prometheus Metrics, and
    | Distributed Request Tracing across all SaaS modules.
    |
    */

    'enabled' => env('MONITORING_ENABLED', true),

    'health' => [
        'route' => '/health',
        'live_route' => '/health/live',
        'ready_route' => '/health/ready',
        'disk_space_min_mb' => env('HEALTH_DISK_MIN_MB', 512),
        'check_stripe' => env('HEALTH_CHECK_STRIPE', false),
    ],

    'prometheus' => [
        'enabled' => env('PROMETHEUS_METRICS_ENABLED', true),
        'route' => '/metrics',
        'bearer_token' => env('PROMETHEUS_BEARER_TOKEN', null), // Null = public or internal VPC scraper
        'namespace' => 'saas_platform',
    ],

    'tracing' => [
        'header_name' => 'X-Correlation-Id',
        'forward_headers' => [
            'X-Request-Id',
            'X-Correlation-Id',
        ],
    ],

    'audit' => [
        'retention_days' => 90,
    ],
];
