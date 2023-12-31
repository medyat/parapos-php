<?php

use MedyaT\Parapos\Config\Config;
use MedyaT\Parapos\Config\HttpResponse;
use MedyaT\Parapos\Middlewares\PaymentBinMiddleware;
use MedyaT\Parapos\Models\Payment;

it('can create payment whire request', function () {

    $httpClient = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [$config = new Config([
        'apiUrl' => 'https://www.bayi.biz/api',
        'secretKey' => 'asd',
        'apiKey' => '',
    ])]);

    $httpResponse = '{"data":{"card_code":"DIGER","bank_name":"DIGER","bank_code":"999999","bin":"123456","card_type":"DIGER","holder_type":"PERSONAL","credit_type":"DEBIT","max_installment":1,"is_foreign_card":1,"installments":{"data":[{"installment":1,"plus_installment":0,"monthly_amount":123.45,"total_amount":123.45,"ratio":0}]}}}';

    $payment = new Payment();
    $payment->save();

    $httpClient
        ->shouldReceive('call')
        ->with(Payment::class, 'installment', 'POST', [], ['bin' => '123456', 'amount' => 123.45])
        ->andReturn(new HttpResponse($payment, $httpResponse, []));

    //        $httpClient = new \MedyaT\Parapos\Config\Http($config);

    $cardService = new \MedyaT\Parapos\Services\CardService($config, $httpClient);

    $installmentResponse = $cardService
        ->middleware(PaymentBinMiddleware::class)
        ->installment(
            bin: '123456',
            amount: 123.45,
            payment_id: $payment->id,
        );

    $json_decode_http_response = json_decode($httpResponse, true);
    $json_decode_http_response['data']['payment_id'] = 1;

    expect($installmentResponse)
        ->toBeArray()
        ->toEqual($json_decode_http_response);

    expect(Payment::count())->toBe(1);

});

it('can create payment', function () {

    $payment = new Payment();

    $payment->parapos_code = 'ok';

    $payment->save();

    expect($payment->exists())->toBeTrue();
});
