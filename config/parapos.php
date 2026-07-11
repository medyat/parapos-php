<?php

return [
    /*
     * The Eloquent model backing the `payments` table. Point this at your own
     * model (e.g. a uuid-keyed, tenant-aware one) as long as it
     * `use`s MedyaT\Parapos\Concerns\InteractsWithParaposPayment and
     * `implements` MedyaT\Parapos\Contracts\PaymentStatus.
     */
    'model' => \MedyaT\Parapos\Models\Payment::class,

    /*
     * Register the package's default (bigint) payments migration. Set to false
     * when the host app owns the schema — e.g. a uuid primary key with a
     * tenant_id column — and provides its own migration instead.
     */
    'load_migrations' => true,

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
