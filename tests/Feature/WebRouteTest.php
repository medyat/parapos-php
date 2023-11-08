<?php

use MedyaT\Parapos\Models\Payment;
use Tests\TestLaravelMiddleware;

it('can test web middleware', function () {

    config()->set('parapos.route_middlewares', []);

    app()->make('router')->middlewareGroup('parapos-middleware', config('parapos.route_middlewares'));

    $this->post('/parapos/response/fail', [
        'result_code' => 'OK',
        'result_message' => 'OK',
    ])
        ->assertStatus(404);

});

it('can test web two variables route', function () {

    config()->set('parapos.route_middlewares', [
        TestLaravelMiddleware::class,
    ]);

    app()->make('router')->middlewareGroup('parapos-middleware', config('parapos.route_middlewares'));

    $this->post('/parapos/response/fail/sad', [
        'result_code' => 'OK',
        'result_message' => 'OK',
    ])
        ->assertStatus(444);

    $this->post('/parapos/response/fail', [
        'result_code' => 'OK',
        'result_message' => 'OK',
    ])
        ->assertStatus(444);

    $this->post('/parapos/response/hash/tenant-fail', [
        'result_code' => 'OK',
        'result_message' => 'OK',
    ])
        ->assertStatus(445);

});

it('can test web route', function () {

    $payment = new Payment();
    $payment->response_hash = 'asd';
    $payment->status = Payment::PAYMENT_PENDING;
    $payment->save();

    $route = config('parapos.response_url', 'parapos/response/{hash}');

    expect($route)->toBe('parapos/response/{hash}/{tenant?}');

    $this->post('/parapos/response/'.$payment->response_hash, [
        'result_code' => $code = '1024523049834095',
        'result_message' => $message = 'this is fail message',
    ])
        ->assertSee('paymentResponse')
        ->assertSee($message)
        ->assertSee($code)
        ->assertOk();

    $payment->refresh();

    expect($payment->status)->toBe(Payment::PAYMENT_FAIL);

});
