<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe & SaaS Billing Configuration
    |--------------------------------------------------------------------------
    |
    | Defines subscription tiers, monthly quotas, and Stripe price bindings.
    |
    */

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'plans' => [
        'starter' => [
            'name' => 'Starter Plan',
            'stripe_price_id' => env('STRIPE_STARTER_PRICE_ID', 'price_starter_test'),
            'price_usd' => 19,
            'features' => [
                'seats' => 2,
                'ai_credits_monthly' => 100,
                'api_access' => false,
            ],
        ],
        'pro' => [
            'name' => 'Pro Plan',
            'stripe_price_id' => env('STRIPE_PRO_PRICE_ID', 'price_pro_test'),
            'price_usd' => 49,
            'features' => [
                'seats' => 10,
                'ai_credits_monthly' => 1000,
                'api_access' => true,
            ],
        ],
        'enterprise' => [
            'name' => 'Enterprise Plan',
            'stripe_price_id' => env('STRIPE_ENTERPRISE_PRICE_ID', 'price_enterprise_test'),
            'price_usd' => 199,
            'features' => [
                'seats' => 50,
                'ai_credits_monthly' => 10000,
                'api_access' => true,
                'dedicated_support' => true,
            ],
        ],
    ],
];
