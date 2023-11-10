<?php

use MedyaT\Parapos\Config\Config;
use MedyaT\Parapos\Config\HttpResponse;
use MedyaT\Parapos\Models\Payment;
use MedyaT\Parapos\Parapos;
use MedyaT\Parapos\Services\PosService;

it('pos service methods', function () {

    $parapos = new Parapos();

    $posService = $parapos->pos();

    expect($posService)
        ->toBeInstanceOf(PosService::class)
        ->toHaveMethods(['getRatios']);

});

it('can pos service', function () {

    $response = [
        'id' => 1,
        'title' => 'Pos Ratios',
        'status' => 1,
        'ratios' => [
            'data' => [
                [
                    'id' => 9,
                    'pos_id' => 1,
                    'connection_id' => 1,
                    'installment' => 1,
                    'plus_installment' => 0,
                    'ratio' => 0,
                    'bank_ratio' => 2,
                    'foreign_card_ratio' => 2,
                    'min' => 0,
                    'card_code' => 'AXESS',
                ],
                [
                    'id' => 10,
                    'pos_id' => 1,
                    'connection_id' => 1,
                    'installment' => 2,
                    'plus_installment' => 2,
                    'ratio' => 0,
                    'bank_ratio' => 3,
                    'foreign_card_ratio' => 0,
                    'min' => 100,
                    'card_code' => 'AXESS',
                ],
            ],
        ],
    ];

    $http = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [$config = new Config()]);

    $http
        ->shouldReceive('call')
        ->with(Payment::class, 'pos/active', 'GET', [], [])
        ->andReturn(new HttpResponse(payment: new Payment(), response: json_encode($response)));

    $posService = new PosService($config, $http);

    $pos = $posService->getRatios();

    expect($pos)->toBe($response);

    expect(Payment::count())->toBe(0);

});
