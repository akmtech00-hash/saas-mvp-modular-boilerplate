<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Feature Flags
    |--------------------------------------------------------------------------
    |
    | Baseline defaults if not explicitly toggled in database globally or per tenant.
    |
    */
    'defaults' => [
        'api_access' => true,
        'webhooks' => true,
        'ai_gateway' => true,
        'audit_logs' => true,
        'team_invitations' => true,
        'custom_domains' => false,
        'advanced_analytics' => false,
    ],
];
