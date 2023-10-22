<?php

use MedyaT\Parapos\Config\Config;
use MedyaT\Parapos\Config\Http;
use MedyaT\Parapos\Parapos;
use MedyaT\Parapos\Services\CardService;

it('installemt service methods', function () {

    $parapos = new Parapos();

    $installmentService = $parapos->card();

    expect($installmentService)
        ->toBeInstanceOf(CardService::class)
        ->toHaveMethods(['bin', 'installment']);

});

it('can mock http client for bin', function () {

    // mock http client
    $http = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [$config = new Config()]);

    $http
        ->shouldReceive('call')
        ->with('bin', 'POST', [], ['bin' => '123456'])
        ->andReturn('response');

    $installmentService = new CardService($config, $http);

    $binResponse = $installmentService->bin('123456');

    expect($binResponse)
        ->toBeString()
        ->toBe('response');

});

it('can make request for installment method', function () {

    $http = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [$config = new Config()]);

    $http
        ->shouldReceive('call')
        ->with('installment', 'POST', [], ['bin' => '123456', 'amount' => 123.45])
        ->andReturn('response');

    $installmentService = new CardService($config, $http);

    $installmentResponse = $installmentService->installment(bin: '123456', amount: 123.45);

    expect($installmentResponse)
        ->toBeString()
        ->toBe('response');
});

it('can make request for installment method with sub amounts', function () {

    $http = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [$config = new Config()]);

    $http
        ->shouldReceive('call')
        ->with('installment', 'POST', [], ['bin' => '123456', 'amount' => 123.45, 'sub_amounts' => [100, 20, 3.45]])
        ->andReturn('response');

    $installmentService = new CardService($config, $http);

    $installmentResponse = $installmentService->installment(bin: '123456', amount: 123.45, subAmounts: [100, 20, 3.45]);

    expect($installmentResponse)
        ->toBeString()
        ->toBe('response');
});
