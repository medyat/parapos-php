<?php

use Illuminate\Support\Facades\Route;
use MedyaT\Parapos\Contracts\PaymentStatus;
use MedyaT\Parapos\Models\Payment;
use Tests\TestCustomPayment;
use Tests\TestCustomResponseController;

beforeEach(function (): void {
    TestCustomPayment::createTable();
    config()->set('parapos.route_middlewares', []);
    app()->make('router')->middlewareGroup('parapos-middleware', []);
});

it('resolves the configured model in the bundled callback route', function () {

    config()->set('parapos.model', TestCustomPayment::class);

    $payment = new TestCustomPayment;
    $payment->status = PaymentStatus::PAYMENT_PENDING;
    $payment->save();

    $this->post('/parapos/response/'.$payment->response_hash, [
        'result_code' => 'OK',
        'result_message' => 'ok',
    ])->assertOk();

    expect($payment->fresh()->status)->toBe(PaymentStatus::PAYMENT_SUCCESS);

});

it('does not read the default model table when another model is configured', function () {

    config()->set('parapos.model', TestCustomPayment::class);

    $decoy = new Payment;
    $decoy->response_hash = 'SHARED';
    $decoy->status = PaymentStatus::PAYMENT_PENDING;
    $decoy->save();

    $this->post('/parapos/response/SHARED', [
        'result_code' => 'OK',
        'result_message' => 'ok',
    ])->assertNotFound();

    expect($decoy->fresh()->status)->toBe(PaymentStatus::PAYMENT_PENDING);

});

it('lets a host register a second route against its own model', function () {

    config()->set('parapos.model', Payment::class);

    Route::post('custom/response/{hash}', TestCustomResponseController::class);

    $custom = new TestCustomPayment;
    $custom->status = PaymentStatus::PAYMENT_PENDING;
    $custom->save();

    $default = new Payment;
    $default->response_hash = $custom->response_hash;
    $default->status = PaymentStatus::PAYMENT_PENDING;
    $default->save();

    $this->post('/custom/response/'.$custom->response_hash, [
        'result_code' => 'OK',
        'result_message' => 'ok',
    ])->assertOk();

    expect($custom->fresh()->status)->toBe(PaymentStatus::PAYMENT_SUCCESS)
        ->and($default->fresh()->status)->toBe(PaymentStatus::PAYMENT_PENDING);

});

it('leaves the global config untouched after a host route runs', function () {

    config()->set('parapos.model', Payment::class);

    Route::post('custom/response/{hash}', TestCustomResponseController::class);

    $custom = new TestCustomPayment;
    $custom->status = PaymentStatus::PAYMENT_PENDING;
    $custom->save();

    $this->post('/custom/response/'.$custom->response_hash, [
        'result_code' => 'OK',
        'result_message' => 'ok',
    ])->assertOk();

    expect(config('parapos.model'))->toBe(Payment::class);

});
