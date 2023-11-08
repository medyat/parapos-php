<?php

return [
    'isTest' => false,
    'apiKey' => '',
    'secretKey' => '',
    'language' => 'tr',
    'isMarketplace' => false,
    'response_middlewares' => [
        \MedyaT\Parapos\Middlewares\VerifyResponseMiddleware::class,
    ],
    'route_middlewares' => [],
    'response_url' => 'parapos/response/{hash}/{tenant?}',
    'view' => 'parapos::response',
    'tenant' => null,
];
