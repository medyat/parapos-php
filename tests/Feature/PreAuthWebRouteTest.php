<?php

use MedyaT\Parapos\Models\Payment;

it('response route sets status to pre_authorized when is_pre_auth payment gets OK', function () {

    config()->set('parapos.route_middlewares', []);
    app()->make('router')->middlewareGroup('parapos-middleware', config('parapos.route_middlewares'));

    $payment = new Payment;
    $payment->response_hash = 'preauth-test-hash';
    $payment->status = Payment::PAYMENT_PENDING;
    $payment->is_pre_auth = 1;
    $payment->save();

    $this->post('/parapos/response/'.$payment->response_hash, [
        'result_code' => 'OK',
        'result_message' => 'Success',
    ])
        ->assertOk();

    $payment->refresh();

    expect($payment->status)->toBe(Payment::PAYMENT_PRE_AUTHORIZED);
});

it('response route still sets status to success when result_code is OK', function () {

    config()->set('parapos.route_middlewares', []);
    app()->make('router')->middlewareGroup('parapos-middleware', config('parapos.route_middlewares'));

    $payment = new Payment;
    $payment->response_hash = 'success-test-hash';
    $payment->status = Payment::PAYMENT_PENDING;
    $payment->save();

    $this->post('/parapos/response/'.$payment->response_hash, [
        'result_code' => 'OK',
        'result_message' => 'Success',
    ])
        ->assertOk();

    $payment->refresh();

    expect($payment->status)->toBe(Payment::PAYMENT_SUCCESS);
});

it('response route still sets status to fail when result_code is not OK or PRE_AUTH', function () {

    config()->set('parapos.route_middlewares', []);
    app()->make('router')->middlewareGroup('parapos-middleware', config('parapos.route_middlewares'));

    $payment = new Payment;
    $payment->response_hash = 'fail-test-hash';
    $payment->status = Payment::PAYMENT_PENDING;
    $payment->save();

    $this->post('/parapos/response/'.$payment->response_hash, [
        'result_code' => 'FAIL',
        'result_message' => 'Failed',
    ])
        ->assertOk();

    $payment->refresh();

    expect($payment->status)->toBe(Payment::PAYMENT_FAIL);
});
