<?php

use MedyaT\Parapos\Config\Config;
use MedyaT\Parapos\Config\HttpResponse;
use MedyaT\Parapos\Models\Payment;
use MedyaT\Parapos\Parapos;
use MedyaT\Parapos\Services\CardService;
use Tests\TestCustomPayment;

/**
 * End-to-end cover for the wiring a host actually uses: Parapos -> Config->model
 * -> service -> FindOrNewPaymentAction -> the model that gets written. The unit
 * tests either side of this prove the pieces; these prove they are connected.
 */
beforeEach(function (): void {
    TestCustomPayment::createTable();
    config()->set('parapos.model', Payment::class);
});

it('writes to the instance model rather than the globally configured one', function () {

    $parapos = new Parapos([
        'apiKey' => 'k',
        'secretKey' => 's',
        'model' => TestCustomPayment::class,
    ]);

    $parapos->payment()->addPayment(client_ip: '127.0.0.1', amount: 10.0);

    expect(TestCustomPayment::count())->toBe(1)
        ->and(Payment::count())->toBe(0);

});

it('falls back to the globally configured model when the instance names none', function () {

    $parapos = new Parapos(['apiKey' => 'k', 'secretKey' => 's']);

    $parapos->payment()->addPayment(client_ip: '127.0.0.1', amount: 10.0);

    expect(Payment::count())->toBe(1)
        ->and(TestCustomPayment::count())->toBe(0);

});

it('writes to the instance model from the card service too', function () {

    $config = new Config(['model' => TestCustomPayment::class]);

    $http = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [$config]);
    $http->shouldReceive('call')->andReturnUsing(
        fn (): HttpResponse => new HttpResponse(
            payment: TestCustomPayment::firstOrFail(),
            response: (string) json_encode(['data' => ['bin' => '123456']]),
        )
    );

    (new CardService($config, $http))->bin('123456');

    expect(TestCustomPayment::count())->toBe(1)
        ->and(Payment::count())->toBe(0);

});

it('keeps two profiles apart in one process without touching global config', function () {

    $tenant = new Parapos(['apiKey' => 'k', 'secretKey' => 's', 'model' => TestCustomPayment::class]);
    $admin = new Parapos(['apiKey' => 'k', 'secretKey' => 's']);

    $tenant->payment()->addPayment(client_ip: '127.0.0.1', amount: 10.0);
    $admin->payment()->addPayment(client_ip: '127.0.0.1', amount: 20.0);

    expect(TestCustomPayment::count())->toBe(1)
        ->and(Payment::count())->toBe(1)
        ->and(config('parapos.model'))->toBe(Payment::class);

});

it('keeps the container-bound instance reading the tenant from live config', function () {

    config()->set('app.url', 'https://example.com');

    /*
     * Resolved first, mutated after — the per-request pattern multi-tenant
     * hosts use. The shipped config file carries `'tenant' => null`, so the
     * provider has to drop that key rather than pin "no tenant" on the
     * container-bound instance for its whole lifetime.
     */
    $parapos = app(Parapos::class);

    config()->set('parapos.tenant', 'late-tenant');

    expect($parapos->config->getResponseUrl('abc'))
        ->toBe('https://example.com/parapos/response/abc/late-tenant');

});
