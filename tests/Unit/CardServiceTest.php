<?php

use MedyaT\Parapos\Config\Config;
use MedyaT\Parapos\Config\Http;
use MedyaT\Parapos\Config\HttpResponse;
use MedyaT\Parapos\Models\Payment;
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

    $payment = new Payment(['status' => Payment::PAYMENT_WAITING]);
    $payment->save();

    $http
        ->shouldReceive('call')
        ->with($payment, 'bin', 'POST', [], ['bin' => '123456'])
        ->andReturn(new HttpResponse(payment: $payment, response: json_encode($arrayValue = ['data' => ['bin' => '123456']])));

    $installmentService = new CardService($config, $http);

    $binResponse = $installmentService->bin(bin: '123456', payment_id: $payment->id);

    expect($binResponse)
        ->toBeArray()
        ->toEqual($arrayValue);

});

it('can make request for installment method anothers', function () {

    $http = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [$config = new Config()]);

    $payment = new Payment();
    $payment->save();

    $http
        ->shouldReceive('call')
        ->with($payment, 'installment', 'POST', [], ['bin' => '123456', 'amount' => 123.45])
        ->andReturn(new HttpResponse($payment, json_encode($arrayValue = ['data' => ['installments' => []]])));

    $installmentService = new CardService($config, $http);

    $installmentResponse = $installmentService->installment(bin: '123456', amount: 123.45, payment_id: $payment->id);

    expect($installmentResponse)
        ->toBeArray()
        ->toEqual($arrayValue);
});

it('can make request for installment method with sub amounts', function () {

    $http = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [$config = new Config()]);

    $payment = new Payment();
    $payment->save();

    $http
        ->shouldReceive('call')
        ->with($payment, 'installment', 'POST', [], ['bin' => '123456', 'amount' => 123.45, 'sub_amounts' => [100, 20, 3.45]])
        ->andReturn(new HttpResponse($payment, json_encode($arrayValue = ['data' => ['installments' => [['installment' => 1]]]])));

    $installmentService = new CardService($config, $http);

    $installmentResponse = $installmentService->installment(bin: '123456', amount: 123.45, subAmounts: [100, 20, 3.45], payment_id: $payment->id);

    expect($installmentResponse)
        ->toBeArray()
        ->toEqual($arrayValue);
});
