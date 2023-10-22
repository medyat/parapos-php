<?php

use MedyaT\Parapos\Config\Config;
use MedyaT\Parapos\Middlewares\PaymentMiddleware;
use MedyaT\Parapos\Models\Payment;

it('can create payment whire request', function () {

    $httpClient = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [$config = new Config([
        'apiUrl' => 'https://www.bayi.biz/api',
        'secretKey' => 'asd',
        'apiKey' => '',
    ])]);

    $httpClient
        ->shouldReceive('call')
        ->with('installment', 'POST', [], ['bin' => '123456', 'amount' => 123.45, 'payment_id' => 1])
        ->andReturn($response = '{"data":{"card_code":"DIGER","bank_name":"DIGER","bank_code":"999999","bin":"123456","card_type":"DIGER","holder_type":"PERSONAL","credit_type":"DEBIT","max_installment":1,"is_foreign_card":1,"installments":{"data":[{"installment":1,"plus_installment":0,"monthly_amount":123.45,"total_amount":123.45,"ratio":0}]}}}');

    //        $httpClient = new \MedyaT\Parapos\Config\Http($config);

    $cardService = new \MedyaT\Parapos\Services\CardService($config, $httpClient);

    $installmentResponse = $cardService->middleware(PaymentMiddleware::class)->installment(bin: '123456', amount: 123.45);

    expect($installmentResponse)->toEqual($response);

    expect(Payment::count())->toBe(1);

});

it('can create payment', function () {

    $payment = new Payment();

    $payment->parapos_code = 'ok';

    $payment->save();

    expect($payment->exists())->toBeTrue();
});
