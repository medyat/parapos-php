<?php

use MedyaT\Parapos\Config\Config;
use MedyaT\Parapos\Config\HttpResponse;
use MedyaT\Parapos\Middlewares\PaymentMiddleware;
use MedyaT\Parapos\Services\CardService;

it('can payment with bin request', function () {

    $http = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [$config = new Config()]);

    $firstPayment = \MedyaT\Parapos\Models\Payment::create([
        'amount' => 100,
        'installment' => 1,
    ]);

    $http
        ->shouldReceive('call')
        ->with('bin', 'POST', [], ['bin' => '123456', 'payment_id' => 2])
        ->andReturn(new HttpResponse(json_encode($arrayValue = ['data' => ['installments' => [['installment' => 1]]]])));

    $installmentService = new CardService($config, $http);

    $installmentService = $installmentService->middleware(PaymentMiddleware::class);

    $installmentResponse = $installmentService->bin(bin: '123456');

    // add payment_id to params
    $arrayValue['data']['payment_id'] = 2;

    expect($installmentResponse)
        ->toBeArray()
        ->toEqual($arrayValue);

    expect(\MedyaT\Parapos\Models\Payment::count())
        ->toEqual(2);

    $payment = \MedyaT\Parapos\Models\Payment::find(2);

    expect($payment->amount)
        ->toEqual(0);

    expect($payment->bin)
        ->toEqual('123456');

});
it('can update payment with installments request', function () {

    $http = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [$config = new Config()]);

    \MedyaT\Parapos\Models\Payment::create([
        'amount' => 100,
        'installment' => 1,
    ]);

    $payment = \MedyaT\Parapos\Models\Payment::create([
        'amount' => 100,
        'installment' => 1,
    ]);

    $http
        ->shouldReceive('call')
        ->with('installment', 'POST', [], ['bin' => '123456', 'amount' => 123.45, 'payment_id' => $payment->id])
        ->andReturn(new HttpResponse(json_encode($arrayValue = ['data' => ['installments' => [['installment' => 1]]]])));

    $installmentService = new CardService($config, $http);

    $installmentService = $installmentService->middleware(PaymentMiddleware::class);

    $installmentResponse = $installmentService->installment(bin: '123456', amount: 123.45, payment_id: $payment->id);

    // add payment_id to params
    $arrayValue['data']['payment_id'] = $payment->id;

    expect($installmentResponse)
        ->toBeArray()
        ->toEqual($arrayValue);

    expect(\MedyaT\Parapos\Models\Payment::count())
        ->toEqual(2);

    $payment = $payment->fresh();

    expect($payment->amount)
        ->toEqual(123.45);

});
