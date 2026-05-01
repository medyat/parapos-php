<?php

use MedyaT\Parapos\Config\Config;
use MedyaT\Parapos\Config\Http;
use MedyaT\Parapos\Config\HttpResponse;
use MedyaT\Parapos\Models\Payment;
use MedyaT\Parapos\Parapos;
use MedyaT\Parapos\Services\PaymentService;

it('payment service has pay3dInit and pay3dPostAuth methods', function () {

    $parapos = new Parapos(['apiUrl' => 'https://bayi.biz']);

    $payment = $parapos->payment();

    expect($payment)
        ->toHaveMethods(['pay3dInit', 'pay3dPostAuth']);

});

it('pay3dInit with is_pre_auth posts to pay_3d_init endpoint and flags pre-auth', function () {

    $httpClient = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [new Config]);

    $payment = new Payment;
    $payment->save();

    $responseData = json_encode([
        'result_code' => 'OK',
        'result_message' => 'Success',
        'data' => [
            'id' => 123,
            'url' => 'https://service.testmoka.com/abc',
        ],
    ]);

    $httpClient->shouldReceive('call')
        ->once()
        ->withArgs(function ($p, $uri, $method, $headers, $params) {
            return $uri === 'pay_3d_init'
                && $method === 'POST'
                && $params['card_number'] === '5269 5511 2222 3339'
                && (int) $params['is_pre_auth'] === 1;
        })
        ->andReturn(new HttpResponse($payment, $responseData, ['http_code' => 200]));

    $config = new Config;
    $service = new PaymentService($config, $httpClient);

    $service->addCard(
        card_number: '5269 5511 2222 3339',
        name: 'John Doe',
        cvv2: '123',
        expire_date_month: '12',
        expire_date_year: '2025'
    );

    $service->addPayment(
        client_ip: '127.0.0.1',
        amount: 100.00,
        installment: 1,
        is_pre_auth: true,
    );

    $result = $service->pay3dInit();

    expect($result)
        ->toHaveKeys(['parapos_code', 'url'])
        ->and($result['parapos_code'])->toBe(123)
        ->and($result['url'])->toBe('https://service.testmoka.com/abc');

    expect((int) $service->payment->is_pre_auth)->toBe(1);

    Mockery::close();
});

it('pay3dPostAuth posts to pay_3d_post_auth endpoint', function () {

    $http = Mockery::mock(Http::class);

    $payment = new Payment;
    $payment->parapos_code = 456;
    $payment->status = Payment::PAYMENT_PRE_AUTHORIZED;
    $payment->save();

    $responseData = json_encode([
        'result_code' => 'OK',
        'result_message' => 'Success',
        'data' => [
            'id' => 456,
        ],
    ]);

    $http->shouldReceive('post')
        ->once()
        ->withArgs(function ($p, $uri, $params) {
            return $uri === 'pay_3d_post_auth'
                && $params['payment_id'] === 456;
        })
        ->andReturn(new HttpResponse($payment, $responseData, ['http_code' => 200]));

    $config = new Config;
    $service = new PaymentService($config, $http);

    $service->payment = $payment;

    $result = $service->pay3dPostAuth();

    expect($result)
        ->toHaveKeys(['result_code', 'result_message'])
        ->and($result['result_code'])->toBe('OK');

    Mockery::close();
});

it('pay3dPostAuth sends sub_dealers when provided', function () {

    $http = Mockery::mock(Http::class);

    $payment = new Payment;
    $payment->parapos_code = 789;
    $payment->status = Payment::PAYMENT_PRE_AUTHORIZED;
    $payment->save();

    $responseData = json_encode([
        'result_code' => 'OK',
        'result_message' => 'Success',
        'data' => [
            'id' => 789,
        ],
    ]);

    $http->shouldReceive('post')
        ->once()
        ->withArgs(function ($p, $uri, $params) {
            return $uri === 'pay_3d_post_auth'
                && $params['payment_id'] === 789
                && count($params['sub_dealers']) === 2
                && $params['sub_dealers'][0]->dealer_id === '1774'
                && $params['sub_dealers'][0]->amount === 60.0;
        })
        ->andReturn(new HttpResponse($payment, $responseData, ['http_code' => 200]));

    $config = new Config;
    $service = new PaymentService($config, $http);

    $service->payment = $payment;

    $service->addDealerAmount(
        dealer_id: '1774',
        amount: 60,
        dealer_commission_amount: 0,
    );
    $service->addDealerAmount(
        dealer_id: '1775',
        amount: 40,
        dealer_commission_amount: 0,
    );

    $result = $service->pay3dPostAuth();

    expect($result['result_code'])->toBe('OK');

    Mockery::close();
});

it('pay3dPostAuth throws exception when no payment is set', function () {

    $config = new Config;
    $service = new PaymentService($config);

    $service->pay3dPostAuth();

})->throws(\MedyaT\Parapos\Exceptions\NoPaymentDefined::class);

it('pay3dInit throws exception when no card is set', function () {

    $config = new Config;
    $service = new PaymentService($config);

    $service->addPayment(
        client_ip: '127.0.0.1',
        amount: 100.00,
        is_pre_auth: true,
    );

    $service->pay3dInit();

})->throws(\MedyaT\Parapos\Exceptions\NoCreditCardDefined::class);

it('pay3dInit throws exception when no payment is set', function () {

    $config = new Config;
    $service = new PaymentService($config);

    $service->addCard(
        card_number: '5269 5511 2222 3339',
        name: 'John Doe',
        cvv2: '123',
        expire_date_month: '12',
        expire_date_year: '2025'
    );

    $service->pay3dInit();

})->throws(\MedyaT\Parapos\Exceptions\NoPaymentDefined::class);

it('payment model has PAYMENT_PRE_AUTHORIZED constant', function () {

    expect(Payment::PAYMENT_PRE_AUTHORIZED)->toBe(3);

});

it('payment model has PAYMENT_PRE_AUTH_CANCELLED constant', function () {

    expect(Payment::PAYMENT_PRE_AUTH_CANCELLED)->toBe(4);

});

it('payment service does not expose cancelPreAuth method (server auto-cancels stale pre-auths)', function () {

    $parapos = new Parapos(['apiUrl' => 'https://bayi.biz']);

    $payment = $parapos->payment();

    expect(method_exists($payment, 'cancelPreAuth'))->toBeFalse();

});

it('payment model has PAYMENT_PRE_AUTH_CANCEL_FAILED constant', function () {

    expect(Payment::PAYMENT_PRE_AUTH_CANCEL_FAILED)->toBe(5);

});

it('payment model has PAYMENT_PRE_AUTH_CANCEL_ABANDONED constant', function () {

    expect(Payment::PAYMENT_PRE_AUTH_CANCEL_ABANDONED)->toBe(6);

});

it('checkStatus posts to payment_status endpoint with client_order_id', function () {

    $http = Mockery::mock(Http::class);

    $payment = new Payment;
    $payment->request_code = 'ORDER-999';
    $payment->status = Payment::PAYMENT_PENDING;
    $payment->save();

    $responseData = json_encode([
        'result_code' => 'OK',
        'result_message' => 'Success',
        'data' => [
            'status' => Payment::PAYMENT_PRE_AUTH_CANCELLED,
        ],
    ]);

    $http->shouldReceive('post')
        ->once()
        ->withArgs(function ($p, $uri, $params) {
            return $uri === 'payment_status'
                && $params['client_order_id'] === 'ORDER-999';
        })
        ->andReturn(new HttpResponse($payment, $responseData, ['http_code' => 200]));

    $config = new Config;
    $service = new PaymentService($config, $http);
    $service->payment = $payment;

    $result = $service->checkStatus();

    expect($result)
        ->toHaveKeys(['status', 'result_code', 'result_message'])
        ->and($result['status'])->toBe(Payment::PAYMENT_PRE_AUTH_CANCELLED)
        ->and($result['result_code'])->toBe('OK');

    // Local model is synced from server response
    expect((int) $service->payment->status)->toBe(Payment::PAYMENT_PRE_AUTH_CANCELLED);
    expect((int) $service->payment->fresh()->status)->toBe(Payment::PAYMENT_PRE_AUTH_CANCELLED);

    Mockery::close();
});

it('checkStatus throws exception when no payment is set', function () {

    $config = new Config;
    $service = new PaymentService($config);

    $service->checkStatus();

})->throws(\MedyaT\Parapos\Exceptions\NoPaymentDefined::class);
