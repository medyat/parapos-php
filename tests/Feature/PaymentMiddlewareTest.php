<?php

use MedyaT\Parapos\Config\Config;
use MedyaT\Parapos\Config\HttpResponse;
use MedyaT\Parapos\Middlewares\PaymentBinMiddleware;
use MedyaT\Parapos\Models\Payment;
use MedyaT\Parapos\Services\CardService;

it('can payment with bin request', function () {

    $http = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [$config = new Config()]);

    $firstPayment = \MedyaT\Parapos\Models\Payment::create([
        'amount' => 100,
        'installment' => 1,
    ]);

    $payment = new Payment();
    $payment->save();

    $http
        ->shouldReceive('call')
        ->with(Payment::class, 'bin', 'POST', [], ['bin' => '123456'])
        ->andReturn(new HttpResponse($payment, json_encode($arrayValue = ['data' => ['installments' => [['installment' => 1]]]])));

    $installmentService = new CardService($config, $http);

    $installmentService = $installmentService->middleware(PaymentBinMiddleware::class);

    $installmentResponse = $installmentService->bin(bin: '123456', payment_id: $payment->id);

    // add payment_id to params
    $arrayValue['data']['payment_id'] = $payment->id;

    expect($installmentResponse)
        ->toBeArray()
        ->toEqual($arrayValue);

    expect(\MedyaT\Parapos\Models\Payment::count())
        ->toEqual(2);

    $payment->refresh();

    expect($payment->amount)
        ->toEqual(0);

    expect($payment->bin)
        ->toEqual('123456');

});

it('can update payment with installments request', function () {

    $http = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [$config = new Config()]);

    $paymentDb = new Payment();
    $paymentDb->save();

    $payment = \MedyaT\Parapos\Models\Payment::create([
        'amount' => 100,
        'installment' => 1,
    ]);

    $http
        ->shouldReceive('call')
        ->with(Payment::class, 'installment', 'POST', [], ['bin' => '123456', 'amount' => 123.45])
        ->andReturn(new HttpResponse($paymentDb, json_encode($arrayValue = ['data' => ['installments' => [['installment' => 1]]]])));

    $installmentService = new CardService($config, $http);

    $installmentService = $installmentService->middleware(PaymentBinMiddleware::class);

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
