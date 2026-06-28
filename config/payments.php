<?php

use App\Payments\Gateways\CashOnDeliveryGateway;
use App\Payments\Gateways\DemoCardGateway;

return [
    'currency' => env('PAYMENTS_CURRENCY', 'USD'),

    'methods' => [
        'demo_card' => DemoCardGateway::class,
        'cash_on_delivery' => CashOnDeliveryGateway::class,
    ],

    'providers' => [
        'demo_card' => [
            'webhook_secret' => env('DEMO_CARD_WEBHOOK_SECRET', 'demo-card-secret'),
        ],
        'cash_on_delivery' => [],
    ],
];
