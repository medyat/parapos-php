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
    'response_url' => 'parapos/response/{hash}',
];
